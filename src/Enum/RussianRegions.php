<?php

declare(strict_types=1);

namespace App\Enum;

enum RussianRegions
{
    // Республики
    case ADYGEA;
    case BASHKORTOSTAN;
    case BURYATIA;
    case ALTAI_REPUBLIC;
    case DAGESTAN;
    case INGUSHETIA;
    case KABARDINO_BALKARIA;
    case KALMYKIA;
    case KARACHAY_CHERKESSIA;
    case KARELIA;
    case KOMI;
    case MARI_EL;
    case MORDOVIA;
    case SAKHA_YAKUTIA;
    case NORTH_OSSETIA_ALANIA;
    case TATARSTAN;
    case TUVA;
    case UDMURTIA;
    case KHAKASSIA;
    case CHECHNYA;
    case CHUVASHIA;
    case CRIMEA;

    // Края
    case ALTAI_KRAI;
    case KRASNODAR_KRAI;
    case KRASNOYARSK_KRAI;
    case PRIMORSKY_KRAI;
    case STAVROPOL_KRAI;
    case KHABAROVSK_KRAI;
    case KAMCHATKA_KRAI;
    case PERM_KRAI;
    case ZABAIKALSKY_KRAI;

    // Области
    case AMUR_OBLAST;
    case ARKHANGELSK_OBLAST;
    case ASTRAKHAN_OBLAST;
    case BELGOROD_OBLAST;
    case BRYANSK_OBLAST;
    case VLADIMIR_OBLAST;
    case VOLGOGRAD_OBLAST;
    case VOLOGDA_OBLAST;
    case VORONEZH_OBLAST;
    case IVANOVO_OBLAST;
    case IRKUTSK_OBLAST;
    case KALININGRAD_OBLAST;
    case KALUGA_OBLAST;
    case KEMEROVO_OBLAST;
    case KIROV_OBLAST;
    case KOSTROMA_OBLAST;
    case KURGAN_OBLAST;
    case KURSK_OBLAST;
    case LENINGRAD_OBLAST;
    case LIPETSK_OBLAST;
    case MAGADAN_OBLAST;
    case MOSCOW_OBLAST;
    case MURMANSK_OBLAST;
    case NIZHNY_NOVGOROD_OBLAST;
    case NOVGOROD_OBLAST;
    case NOVOSIBIRSK_OBLAST;
    case OMSK_OBLAST;
    case ORENBURG_OBLAST;
    case ORYOL_OBLAST;
    case PENZA_OBLAST;
    case PSKOV_OBLAST;
    case ROSTOV_OBLAST;
    case RYAZAN_OBLAST;
    case SAMARA_OBLAST;
    case SARATOV_OBLAST;
    case SAKHALIN_OBLAST;
    case SVERDLOVSK_OBLAST;
    case SMOLENSK_OBLAST;
    case TAMBOV_OBLAST;
    case TVER_OBLAST;
    case TOMSK_OBLAST;
    case TULA_OBLAST;
    case TYUMEN_OBLAST;
    case ULYANOVSK_OBLAST;
    case CHELYABINSK_OBLAST;
    case YAROSLAVL_OBLAST;
    case DONETSK_PEOPLE_REPUBLIC;
    case LUGANSK_PEOPLE_REPUBLIC;
    case KHERSON_OBLAST;
    case ZAPOROZHYE_OBLAST;

    // Автономные округа / города федерального значения / прочее
    case MOSCOW;
    case SAINT_PETERSBURG;
    case JEWISH_AUTONOMOUS_OBLAST;
    case NENETS_AUTONOMOUS_OKRUG;
    case KHANTY_MANSI_AUTONOMOUS_OKRUG;
    case CHUKOTKA_AUTONOMOUS_OKRUG;
    case YAMALO_NENETS_AUTONOMOUS_OKRUG;
    case SEVASTOPOL;
    case OVERSEAS_TERRITORIES;

    /**
     * Возвращает массив кодов региона (строки, с ведущими нулями)
     */
    public function getCodes(): array
    {
        return match ($this) {
            self::ADYGEA => ['01', '101'],
            self::BASHKORTOSTAN => ['02', '102', '702'],
            self::BURYATIA => ['03', '103'],
            self::ALTAI_REPUBLIC => ['04', '104'],
            self::DAGESTAN => ['05', '105'],
            self::INGUSHETIA => ['06', '106'],
            self::KABARDINO_BALKARIA => ['07', '107'],
            self::KALMYKIA => ['08', '108'],
            self::KARACHAY_CHERKESSIA => ['09', '109'],
            self::KARELIA => ['10', '110'],
            self::KOMI => ['11', '111'],
            self::MARI_EL => ['12', '112'],
            self::MORDOVIA => ['13', '113'],
            self::SAKHA_YAKUTIA => ['14', '114'],
            self::NORTH_OSSETIA_ALANIA => ['15', '115'],
            self::TATARSTAN => ['16', '116', '716'],
            self::TUVA => ['17', '117'],
            self::UDMURTIA => ['18', '118'],
            self::KHAKASSIA => ['19', '119'],
            self::CHECHNYA => ['20', '95', '195'],
            self::CHUVASHIA => ['21', '121'],
            self::ALTAI_KRAI => ['22', '122'],
            self::KRASNODAR_KRAI => ['23', '93', '123', '193'],
            self::KRASNOYARSK_KRAI => ['24', '84', '88', '124'],
            self::PRIMORSKY_KRAI => ['25', '125', '725'],
            self::STAVROPOL_KRAI => ['26', '126'],
            self::KHABAROVSK_KRAI => ['27', '127'],
            self::AMUR_OBLAST => ['28', '128'],
            self::ARKHANGELSK_OBLAST => ['29', '129'],
            self::ASTRAKHAN_OBLAST => ['30', '130'],
            self::BELGOROD_OBLAST => ['31', '131'],
            self::BRYANSK_OBLAST => ['32', '132'],
            self::VLADIMIR_OBLAST => ['33', '133'],
            self::VOLGOGRAD_OBLAST => ['34', '134'],
            self::VOLOGDA_OBLAST => ['35', '135'],
            self::VORONEZH_OBLAST => ['36', '136'],
            self::IVANOVO_OBLAST => ['37', '137'],
            self::IRKUTSK_OBLAST => ['38', '138'],
            self::KALININGRAD_OBLAST => ['39', '91', '139'],
            self::KALUGA_OBLAST => ['40', '140'],
            self::KAMCHATKA_KRAI => ['41', '141'],
            self::KEMEROVO_OBLAST => ['42', '142'],
            self::KIROV_OBLAST => ['43', '143'],
            self::KOSTROMA_OBLAST => ['44', '144'],
            self::KURGAN_OBLAST => ['45', '145'],
            self::KURSK_OBLAST => ['46', '146'],
            self::LENINGRAD_OBLAST => ['47', '147'],
            self::LIPETSK_OBLAST => ['48', '148'],
            self::MAGADAN_OBLAST => ['49', '149'],
            self::MOSCOW_OBLAST => ['50', '90', '150', '190', '250', '550', '750', '790'],
            self::MURMANSK_OBLAST => ['51', '151'],
            self::NIZHNY_NOVGOROD_OBLAST => ['52', '152', '252'],
            self::NOVGOROD_OBLAST => ['53', '153'],
            self::NOVOSIBIRSK_OBLAST => ['54', '154', '754'],
            self::OMSK_OBLAST => ['55', '155'],
            self::ORENBURG_OBLAST => ['56', '156'],
            self::ORYOL_OBLAST => ['57', '157'],
            self::PENZA_OBLAST => ['58', '158'],
            self::PERM_KRAI => ['59', '159'],
            self::PSKOV_OBLAST => ['60'],
            self::ROSTOV_OBLAST => ['61', '161', '761'],
            self::RYAZAN_OBLAST => ['62', '162'],
            self::SAMARA_OBLAST => ['63', '163', '763'],
            self::SARATOV_OBLAST => ['64', '164'],
            self::SAKHALIN_OBLAST => ['65', '165'],
            self::SVERDLOVSK_OBLAST => ['66', '96', '166', '196'],
            self::SMOLENSK_OBLAST => ['67', '167'],
            self::TAMBOV_OBLAST => ['68', '168'],
            self::TVER_OBLAST => ['69', '169'],
            self::TOMSK_OBLAST => ['70', '170'],
            self::TULA_OBLAST => ['71', '171'],
            self::TYUMEN_OBLAST => ['72', '172'],
            self::ULYANOVSK_OBLAST => ['73', '173'],
            self::CHELYABINSK_OBLAST => ['74', '174', '774'],
            self::ZABAIKALSKY_KRAI => ['75', '175'],
            self::YAROSLAVL_OBLAST => ['76', '176'],
            self::MOSCOW => ['77', '97', '99', '177', '197', '199', '277', '299', '777', '797', '799', '977'],
            self::SAINT_PETERSBURG => ['78', '98', '178', '198', '778'],
            self::JEWISH_AUTONOMOUS_OBLAST => ['79', '179'],
            self::DONETSK_PEOPLE_REPUBLIC => ['80', '180'],
            self::LUGANSK_PEOPLE_REPUBLIC => ['81', '181'],
            self::CRIMEA => ['82', '182'],
            self::NENETS_AUTONOMOUS_OKRUG => ['83', '183'],
            self::KHERSON_OBLAST => ['84', '184'],
            self::ZAPOROZHYE_OBLAST => ['85', '185'],
            self::KHANTY_MANSI_AUTONOMOUS_OKRUG => ['86', '186'],
            self::CHUKOTKA_AUTONOMOUS_OKRUG => ['87', '187'],
            self::YAMALO_NENETS_AUTONOMOUS_OKRUG => ['89', '189'],
            self::SEVASTOPOL => ['92', '192'],
            self::OVERSEAS_TERRITORIES => ['94', '194'],
        };
    }

    /**
     * Возвращает официальное название региона
     */
    public function getName(): string
    {
        return match ($this) {
            self::ADYGEA => 'Республика Адыгея',
            self::BASHKORTOSTAN => 'Республика Башкортостан',
            self::BURYATIA => 'Республика Бурятия',
            self::ALTAI_REPUBLIC => 'Республика Алтай',
            self::DAGESTAN => 'Республика Дагестан',
            self::INGUSHETIA => 'Республика Ингушетия',
            self::KABARDINO_BALKARIA => 'Кабардино-Балкарская Республика',
            self::KALMYKIA => 'Республика Калмыкия',
            self::KARACHAY_CHERKESSIA => 'Карачаево-Черкесская Республика',
            self::KARELIA => 'Республика Карелия',
            self::KOMI => 'Республика Коми',
            self::MARI_EL => 'Республика Марий Эл',
            self::MORDOVIA => 'Республика Мордовия',
            self::SAKHA_YAKUTIA => 'Республика Саха (Якутия)',
            self::NORTH_OSSETIA_ALANIA => 'Республика Северная Осетия — Алания',
            self::TATARSTAN => 'Республика Татарстан',
            self::TUVA => 'Республика Тыва',
            self::UDMURTIA => 'Удмуртская Республика',
            self::KHAKASSIA => 'Республика Хакасия',
            self::CHECHNYA => 'Чеченская Республика',
            self::CHUVASHIA => 'Чувашская Республика',
            self::ALTAI_KRAI => 'Алтайский край',
            self::KRASNODAR_KRAI => 'Краснодарский край',
            self::KRASNOYARSK_KRAI => 'Красноярский край',
            self::PRIMORSKY_KRAI => 'Приморский край',
            self::STAVROPOL_KRAI => 'Ставропольский край',
            self::KHABAROVSK_KRAI => 'Хабаровский край',
            self::AMUR_OBLAST => 'Амурская область',
            self::ARKHANGELSK_OBLAST => 'Архангельская область',
            self::ASTRAKHAN_OBLAST => 'Астраханская область',
            self::BELGOROD_OBLAST => 'Белгородская область',
            self::BRYANSK_OBLAST => 'Брянская область',
            self::VLADIMIR_OBLAST => 'Владимирская область',
            self::VOLGOGRAD_OBLAST => 'Волгоградская область',
            self::VOLOGDA_OBLAST => 'Вологодская область',
            self::VORONEZH_OBLAST => 'Воронежская область',
            self::IVANOVO_OBLAST => 'Ивановская область',
            self::IRKUTSK_OBLAST => 'Иркутская область',
            self::KALININGRAD_OBLAST => 'Калининградская область',
            self::KALUGA_OBLAST => 'Калужская область',
            self::KAMCHATKA_KRAI => 'Камчатский край',
            self::KEMEROVO_OBLAST => 'Кемеровская область',
            self::KIROV_OBLAST => 'Кировская область',
            self::KOSTROMA_OBLAST => 'Костромская область',
            self::KURGAN_OBLAST => 'Курганская область',
            self::KURSK_OBLAST => 'Курская область',
            self::LENINGRAD_OBLAST => 'Ленинградская область',
            self::LIPETSK_OBLAST => 'Липецкая область',
            self::MAGADAN_OBLAST => 'Магаданская область',
            self::MOSCOW_OBLAST => 'Московская область',
            self::MURMANSK_OBLAST => 'Мурманская область',
            self::NIZHNY_NOVGOROD_OBLAST => 'Нижегородская область',
            self::NOVGOROD_OBLAST => 'Новгородская область',
            self::NOVOSIBIRSK_OBLAST => 'Новосибирская область',
            self::OMSK_OBLAST => 'Омская область',
            self::ORENBURG_OBLAST => 'Оренбургская область',
            self::ORYOL_OBLAST => 'Орловская область',
            self::PENZA_OBLAST => 'Пензенская область',
            self::PERM_KRAI => 'Пермский край',
            self::PSKOV_OBLAST => 'Псковская область',
            self::ROSTOV_OBLAST => 'Ростовская область',
            self::RYAZAN_OBLAST => 'Рязанская область',
            self::SAMARA_OBLAST => 'Самарская область',
            self::SARATOV_OBLAST => 'Саратовская область',
            self::SAKHALIN_OBLAST => 'Сахалинская область',
            self::SVERDLOVSK_OBLAST => 'Свердловская область',
            self::SMOLENSK_OBLAST => 'Смоленская область',
            self::TAMBOV_OBLAST => 'Тамбовская область',
            self::TVER_OBLAST => 'Тверская область',
            self::TOMSK_OBLAST => 'Томская область',
            self::TULA_OBLAST => 'Тульская область',
            self::TYUMEN_OBLAST => 'Тюменская область',
            self::ULYANOVSK_OBLAST => 'Ульяновская область',
            self::CHELYABINSK_OBLAST => 'Челябинская область',
            self::ZABAIKALSKY_KRAI => 'Забайкальский край',
            self::YAROSLAVL_OBLAST => 'Ярославская область',
            self::MOSCOW => 'Москва',
            self::SAINT_PETERSBURG => 'Санкт-Петербург',
            self::JEWISH_AUTONOMOUS_OBLAST => 'Еврейская автономная область',
            self::DONETSK_PEOPLE_REPUBLIC => 'Донецкая Народная Республика',
            self::LUGANSK_PEOPLE_REPUBLIC => 'Луганская Народная Республика',
            self::CRIMEA => 'Республика Крым',
            self::NENETS_AUTONOMOUS_OKRUG => 'Ненецкий автономный округ',
            self::KHERSON_OBLAST => 'Херсонская область',
            self::ZAPOROZHYE_OBLAST => 'Запорожская область',
            self::KHANTY_MANSI_AUTONOMOUS_OKRUG => 'Ханты-Мансийский автономный округ',
            self::CHUKOTKA_AUTONOMOUS_OKRUG => 'Чукотский автономный округ',
            self::YAMALO_NENETS_AUTONOMOUS_OKRUG => 'Ямало-Ненецкий автономный округ',
            self::SEVASTOPOL => 'Севастополь',
            self::OVERSEAS_TERRITORIES => 'Зарубежные территории, обслуживаемые МВД РФ, Байконур',
        };
    }

    /**
     * Первый (основной) код региона
     */
    public function getPrimaryCode(): string
    {
        return $this->getCodes()[0];
    }

    /**
     * Поиск региона по коду (с ведущими нулями)
     */
    public static function fromCode(string $code): ?self
    {
        $code = str_pad($code, 2, '0', STR_PAD_LEFT);

        foreach (self::cases() as $region) {
            if (in_array($code, $region->getCodes(), true)) {
                return $region;
            }
        }

        return null;
    }

    /**
     * Поиск с выбросом исключения, если не найден
     */
    public static function fromCodeOrFail(string $code): self
    {
        return self::fromCode($code) ?? throw new \InvalidArgumentException(
            sprintf('Регион с кодом "%s" не найден', $code)
        );
    }

    /**
     * Все коды всех регионов (плоский список)
     */
    public static function allCodes(): array
    {
        $codes = [];
        foreach (self::cases() as $region) {
            $codes = array_merge($codes, $region->getCodes());
        }
        return $codes;
    }
}
