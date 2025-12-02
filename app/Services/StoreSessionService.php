<?php

namespace App\Services;

use App\Models\StoreSession;
use App\Repositories\StoreSessionRepository;
use App\Repositories\TransactionRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class StoreSessionService extends BaseService
{
    public function __construct(
        protected StoreSessionRepository $storeSessionRepository,
        protected TransactionRepository $transactionRepository
    ) {}

    public function openSession(array $data): array
    {
        return $this->executeTransaction(function () use ($data) {
            $existingSession = $this->storeSessionRepository->getOpenSessionForCashier(
                $data['cashier_id'],
                $data['store_id']
            );

            if ($existingSession) {
                throw new Exception('You already have an open session. Please close it first.');
            }

            $data['session_number'] = $this->storeSessionRepository->generateSessionNumber($data['store_id']);
            $data['session_date'] = now()->toDateString();
            $data['status'] = 'open';
            $data['opened_at'] = now();
            $data['tenant_id'] = auth()->user()->tenant_id;

            $session = $this->storeSessionRepository->create($data);

            return $this->successResponse('Session opened successfully', $session);
        });
    }

    public function closeSession(int $sessionId, array $data): array
    {
        return $this->executeTransaction(function () use ($sessionId, $data) {
            $session = $this->storeSessionRepository->findById($sessionId);

            if (!$session) {
                throw new Exception('Session not found');
            }

            if ($session->status !== 'open') {
                throw new Exception('Only open sessions can be closed');
            }

            $salesData = $this->transactionRepository->getTotalSalesBySession($sessionId);

            $expectedCash = $session->opening_cash + $salesData['cash_sales'];
            $actualCash = $data['actual_cash'];
            $variance = $actualCash - $expectedCash;

            $updateData = [
                'closing_cash' => $data['closing_cash'] ?? $actualCash,
                'expected_cash' => $expectedCash,
                'actual_cash' => $actualCash,
                'variance' => $variance,
                'variance_reason' => $data['variance_reason'] ?? null,
                'closed_at' => now(),
            ];

            if ($variance != 0) {
                $updateData['status'] = 'pending_approval';
            } else {
                $updateData['status'] = 'closed';
            }

            $this->storeSessionRepository->update($session, $updateData);

            $message = $variance != 0
                ? 'Session closed with variance. Pending approval.'
                : 'Session closed successfully';

            return $this->successResponse($message, $session->fresh());
        });
    }

    public function approveSession(int $sessionId, array $data): array
    {
        return $this->executeTransaction(function () use ($sessionId, $data) {
            $session = $this->storeSessionRepository->findById($sessionId);

            if (!$session) {
                throw new Exception('Session not found');
            }

            if ($session->status !== 'pending_approval') {
                throw new Exception('Only pending sessions can be approved');
            }

            $this->storeSessionRepository->update($session, [
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $data['approval_notes'] ?? null,
            ]);

            return $this->successResponse('Session approved successfully', $session->fresh());
        });
    }

    public function getSessionDetails(int $sessionId): array
    {
        $session = $this->storeSessionRepository->findById($sessionId);

        if (!$session) {
            throw new Exception('Session not found');
        }

        $salesData = $this->transactionRepository->getTotalSalesBySession($sessionId);

        return [
            'session' => $session,
            'sales_data' => $salesData,
        ];
    }

    public function getCurrentSession(int $cashierId, int $storeId): ?StoreSession
    {
        return $this->storeSessionRepository->getOpenSessionForCashier($cashierId, $storeId);
    }
}
