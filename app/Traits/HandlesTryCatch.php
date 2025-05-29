<?php


namespace App\Traits;


use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HandlesTryCatch
{


    /**
     * Execute logic with try-catch and return a JSON response.
     *
     * @param callable $logic The logic to execute.
     * @param string|null $successMessage The success message to return.
     * @param string|null $errorMessage The error message to return.
     * @return JsonResponse
     */
    protected function executeWithTryCatch(callable $logic, $successMessage = null, $errorMessage = null): JsonResponse
    {
        try {
            // Execute the logic
            call_user_func($logic);

            // Return a success JSON response
            return response()->json([
                'status' => 'success',
                'message' => $successMessage,
            ]);
        } catch (Exception $e) {

            Log::error('Failed to execute logic: ' . $e->getMessage());
            // Return an error JSON response
            return response()->json([
                'status' => 'error',
                'message' => $errorMessage ?? 'An error occurred while processing your request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    protected function handleTransaction(callable $logic, $successMessage = null, $errorMessage = null)
    {
        try {
            // Begin the database transaction
            DB::beginTransaction();

            // Execute the logic
            $result = call_user_func($logic);

            // Commit the transaction if everything is successful
            DB::commit();

            // Return a success JSON response
//            return $result ?? $this->getResponse(data: $result);
            return $result;
        } catch (Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollBack();

            // Log the error
            Log::error('Failed to execute logic: ' . $e);
            throw $e;


        }
    }

}
