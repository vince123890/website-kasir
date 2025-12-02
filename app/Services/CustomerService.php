<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Exception;

class CustomerService extends BaseService
{
    public function __construct(
        protected CustomerRepository $customerRepository
    ) {}

    public function createCustomer(array $data): array
    {
        return $this->executeTransaction(function () use ($data) {
            $existingCustomer = $this->customerRepository->findByPhone($data['phone']);

            if ($existingCustomer) {
                throw new Exception('Customer with this phone number already exists');
            }

            $data['tenant_id'] = auth()->user()->tenant_id;
            $data['is_active'] = $data['is_active'] ?? true;
            $data['loyalty_points'] = 0;

            $customer = $this->customerRepository->create($data);

            return $this->successResponse('Customer created successfully', $customer);
        });
    }

    public function updateCustomer(int $id, array $data): array
    {
        return $this->executeTransaction(function () use ($id, $data) {
            $customer = $this->customerRepository->findById($id);

            if (!$customer) {
                throw new Exception('Customer not found');
            }

            if (isset($data['phone']) && $data['phone'] !== $customer->phone) {
                $existingCustomer = $this->customerRepository->findByPhone($data['phone']);
                if ($existingCustomer) {
                    throw new Exception('Phone number already used by another customer');
                }
            }

            $this->customerRepository->update($customer, $data);

            return $this->successResponse('Customer updated successfully', $customer->fresh());
        });
    }

    public function deleteCustomer(int $id): array
    {
        return $this->executeTransaction(function () use ($id) {
            $customer = $this->customerRepository->findById($id);

            if (!$customer) {
                throw new Exception('Customer not found');
            }

            if ($customer->transactions_count > 0) {
                throw new Exception('Cannot delete customer with transaction history. Please deactivate instead.');
            }

            $this->customerRepository->delete($customer);

            return $this->successResponse('Customer deleted successfully');
        });
    }

    public function searchByPhone(string $phone): array
    {
        $customers = $this->customerRepository->searchByPhone($phone);

        return [
            'success' => true,
            'data' => $customers,
        ];
    }

    public function getCustomerStats(int $customerId): array
    {
        $stats = $this->customerRepository->getCustomerStats($customerId);

        return [
            'success' => true,
            'data' => $stats,
        ];
    }

    public function addLoyaltyPoints(int $customerId, int $points): array
    {
        $customer = $this->customerRepository->findById($customerId);

        if (!$customer) {
            throw new Exception('Customer not found');
        }

        $customer->addLoyaltyPoints($points);

        return $this->successResponse('Loyalty points added successfully', $customer->fresh());
    }

    public function deductLoyaltyPoints(int $customerId, int $points): array
    {
        $customer = $this->customerRepository->findById($customerId);

        if (!$customer) {
            throw new Exception('Customer not found');
        }

        if ($customer->loyalty_points < $points) {
            throw new Exception('Insufficient loyalty points');
        }

        $customer->deductLoyaltyPoints($points);

        return $this->successResponse('Loyalty points deducted successfully', $customer->fresh());
    }
}
