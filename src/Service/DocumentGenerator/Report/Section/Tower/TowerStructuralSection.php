<?php

declare(strict_types=1);

namespace App\Service\DocumentGenerator\Report\Section\Tower;

use App\Entity\PlatformSection;
use App\Enum\Pillar\ElementTypeEnum;
use App\Enum\Pillar\SectionConstructTypeEnum;
use App\Service\DocumentGenerator\DocStyleRegistry;
use App\Service\DocumentGenerator\Report\ReportContext;
use App\Service\DocumentGenerator\Report\SectionBuilderInterface;
use PhpOffice\PhpWord\Element\Section;

/**
 * Раздел «Конструктивное решение сооружения» для башни.
 *
 * Описание собирается из секций каркаса: форма ствола (пирамида / призма) по ширинам
 * граней, отметки переходов, базовые размеры и типы сечений поясов, раскосов и распорок.
 */
final class TowerStructuralSection implements SectionBuilderInterface
{
    private const FACETS_ADJECTIVE = [
        3 => 'трёхгранной',
        4 => 'четырёхгранной',
    ];

    /** Элементы, перечисляемые в описании, с подписью во множественном числе */
    private const ELEMENT_TITLES = [
        ElementTypeEnum::BELT->value => 'пояса',
        ElementTypeEnum::BRACE->value => 'раскосы',
        ElementTypeEnum::SPACER->value => 'распорки',
    ];

    private const PROFILE_TITLES = [
        SectionConstructTypeEnum::ROUND_PIPE->value => 'стальные круглые трубы',
        SectionConstructTypeEnum::SQUARE_PIPE->value => 'стальные квадратные трубы',
        SectionConstructTypeEnum::ANGLE->value => 'равнополочные уголки',
        SectionConstructTypeEnum::DOUBLE_ANGLE->value => 'парные уголки',
        SectionConstructTypeEnum::CHANNEL->value => 'швеллеры',
        SectionConstructTypeEnum::CHANNEL_NARROW_FLANGE->value => 'швеллеры',
        SectionConstructTypeEnum::STRIP->value => 'стальные полосы',
        SectionConstructTypeEnum::ROUND->value => 'стальной круг',
        SectionConstructTypeEnum::OTHER->value => 'прочие профили',
    ];

    public function build(Section $section, ReportContext $context, int &$tableNum): void
    {
        $body = DocStyleRegistry::bodyText();
        $para = DocStyleRegistry::paragraphIndent();

        $sections = $this->getFrameSections($context);
        if ($sections === []) {
            $section->addText('[Секции башни не заданы]', $body, $para);
            $section->addTextBreak(1);
            return;
        }

        $facetsCount = $context->getData()?->getTowerSpecificData()?->facetsCount
            ?? $context->calculation->getPlatform()?->getFacetsCount();
        $facets = self::FACETS_ADJECTIVE[$facetsCount] ?? 'многогранной';
        $parts = $this->splitByShape($sections);
        $bottomMark = $this->bottomMark($sections[0]);

        $section->addText(
            sprintf(
                'Ствол башни Н=%s м представляет собой пространственную стержневую конструкцию в виде %s.',
                $context->getHeightM(),
                implode(', ', array_map(
                    fn(array $part): string => sprintf(
                        '%s %s с отм. %s м до отм. %s м',
                        $facets,
                        $part['isPyramid'] ? 'усечённой пирамиды' : 'призмы',
                        $this->formatMark($part['from']),
                        $this->formatMark($part['to']),
                    ),
                    $parts,
                )),
            ),
            $body,
            $para,
        );
        $section->addText(
            sprintf('Башня установлена на ж/б фундамент на отм. %s м.', $this->formatMark($bottomMark)),
            $body,
            $para,
        );

        $section->addText('Базовые размеры башни:', $body, $para);
        foreach ($this->baseDimensions($parts) as $line) {
            $section->addText($line, $body, $para);
        }

        $section->addText($this->sectionsCountText($parts), $body, $para);
        $section->addText(
            'Соединения поясов смежных секций – фланцевое. Соединение решётки выполнено через фасонки на сварке и на болтах.',
            $body,
            $para,
        );

        $section->addText('Элементы башни:', $body, $para);
        $elementLines = $this->elementLines($sections);
        $lastIndex = count($elementLines) - 1;
        foreach ($elementLines as $i => $line) {
            $section->addText($line . ($i === $lastIndex ? '.' : ';'), $body, $para);
        }

        $paragraphs = [
            'Монтажные соединения элементов решётки с поясами башни через фасонки на сварке и на болтах.',
            'Для подъёма на опору предусмотрена вертикальная лестница с корзинчатым ограждением, расположенная внутри ствола башни. Предусмотрены площадки для отдыха и обслуживания оборудования.',
            'Кабельная трасса прокладывается по кабельросту.',
        ];
        foreach ($paragraphs as $paragraph) {
            $section->addText($paragraph, $body, $para);
        }

        $section->addTextBreak(1);
    }

    /** @return PlatformSection[] секции каркаса снизу вверх (без подкосов) */
    private function getFrameSections(ReportContext $context): array
    {
        $platform = $context->calculation->getPlatform();
        if ($platform === null) {
            return [];
        }

        return array_values(array_filter(
            $platform->getSortSectionsByNumber(),
            static fn(PlatformSection $section): bool => ! $section->isStrut(),
        ));
    }

    /**
     * Группирует подряд идущие секции одной формы.
     *
     * @param PlatformSection[] $sections
     * @return array<array{isPyramid: bool, from: float, to: float, count: int, widthBottom: float}>
     */
    private function splitByShape(array $sections): array
    {
        $parts = [];
        foreach ($sections as $section) {
            $isPyramid = $section->getWidthBottom() !== $section->getWidthTop();
            $last = array_key_last($parts);

            if ($last !== null && $parts[$last]['isPyramid'] === $isPyramid) {
                $parts[$last]['to'] = $this->topMark($section);
                $parts[$last]['count']++;
                continue;
            }

            $parts[] = [
                'isPyramid' => $isPyramid,
                'from' => $this->bottomMark($section),
                'to' => $this->topMark($section),
                'count' => 1,
                'widthBottom' => ($section->getWidthBottom() ?? 0) / 1000,
            ];
        }

        return $parts;
    }

    /** @return string[] */
    private function baseDimensions(array $parts): array
    {
        $lines = [];
        foreach ($parts as $i => $part) {
            if ($i === 0 || ! $part['isPyramid']) {
                $lines[] = sprintf(
                    '- на отм. %s м%s – %s м;',
                    $this->formatMark($part['from']),
                    $part['isPyramid'] ? '' : ' и выше',
                    $this->formatNumber($part['widthBottom'], 2),
                );
            }
        }

        $lines[array_key_last($lines)] = rtrim($lines[array_key_last($lines)], ';') . '.';

        return $lines;
    }

    private function sectionsCountText(array $parts): string
    {
        $pyramidCount = array_sum(array_column(array_filter($parts, static fn(array $p): bool => $p['isPyramid']), 'count'));
        $prismCount = array_sum(array_column(array_filter($parts, static fn(array $p): bool => ! $p['isPyramid']), 'count'));
        $total = $pyramidCount + $prismCount;

        $details = array_filter([
            $pyramidCount > 0 ? sprintf('пирамидальных – %d', $pyramidCount) : null,
            $prismCount > 0 ? sprintf('призматических – %d', $prismCount) : null,
        ]);

        return sprintf(
            'Конструктивно башня состоит из %d %s (%s).',
            $total,
            $total % 10 === 1 && $total % 100 !== 11 ? 'секции' : 'секций',
            implode(', ', $details),
        );
    }

    /**
     * Строки вида «- пояса – стальные круглые трубы».
     *
     * @param PlatformSection[] $sections
     * @return string[]
     */
    private function elementLines(array $sections): array
    {
        $profilesByElement = [];
        foreach ($sections as $section) {
            foreach ($section->getElementsDto() ?? [] as $element) {
                $elementKey = $element->elementType->value;
                if (! isset(self::ELEMENT_TITLES[$elementKey])) {
                    continue;
                }
                $profilesByElement[$elementKey][self::PROFILE_TITLES[$element->sectionConstructType->value]] = true;
            }
        }

        $lines = [];
        foreach (self::ELEMENT_TITLES as $elementKey => $title) {
            if (! isset($profilesByElement[$elementKey])) {
                continue;
            }
            $lines[] = sprintf('- %s – %s', $title, $this->joinWithAnd(array_keys($profilesByElement[$elementKey])));
        }

        return $lines;
    }

    /** «a», «a и b», «a, b и c» */
    private function joinWithAnd(array $items): string
    {
        $last = array_pop($items);

        return $items === [] ? $last : implode(', ', $items) . ' и ' . $last;
    }

    private function bottomMark(PlatformSection $section): float
    {
        return ($section->getMountHeightBottom() ?? 0) / 1000;
    }

    private function topMark(PlatformSection $section): float
    {
        return (($section->getMountHeightBottom() ?? 0) + ($section->getHeight() ?? 0)) / 1000;
    }

    /** +64,750 / 0,000 / -1,200 */
    private function formatMark(float $mark): string
    {
        $formatted = $this->formatNumber(abs($mark), 3);

        return match (true) {
            $mark > 0 => '+' . $formatted,
            $mark < 0 => '-' . $formatted,
            default => $formatted,
        };
    }

    private function formatNumber(float $value, int $decimals): string
    {
        return number_format($value, $decimals, ',', '');
    }
}
