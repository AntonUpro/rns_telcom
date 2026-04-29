<?php

declare(strict_types=1);

namespace App\Service\Customer;

use App\Entity\Customer;
use App\Repository\CustomerRepository;

final readonly class CustomerService
{
    public function __construct(
        private CustomerRepository $customerRepository,
    ) {
    }

    public function getActiveList(): array
    {
        return array_map(
            static fn(Customer $customer): array => [
                'id'   => $customer->getId(),
                'name' => $customer->getName(),
            ],
            $this->customerRepository->findAllActive()
        );
    }
}
