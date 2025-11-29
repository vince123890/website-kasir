<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class BaseService
{
    /**
     * Execute a database transaction
     */
    protected function executeTransaction(callable $callback)
    {
        try {
            DB::beginTransaction();

            $result = $callback();

            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();

            $this->logError($e);

            throw $e;
        }
    }

    /**
     * Log an error
     */
    protected function logError(Exception $e): void
    {
        Log::error($e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    /**
     * Dispatch an event
     */
    protected function dispatchEvent(string $event, array $data = []): void
    {
        event(new $event(...$data));
    }

    /**
     * Handle service errors
     */
    protected function handleError(Exception $e, string $defaultMessage = 'An error occurred')
    {
        $this->logError($e);

        return [
            'success' => false,
            'message' => config('app.debug') ? $e->getMessage() : $defaultMessage,
        ];
    }

    /**
     * Return success response
     */
    protected function successResponse(string $message, $data = null): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }
}
