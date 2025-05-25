<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ExceptionLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
const EXCEPTION_CONTROLLER = " Http / Exception Controller";
class ExceptionController extends Controller
{
    
    /**
     * The function `exceptionList` takes a request object, retrieves a list of exceptions based on search
     * parameters, and handles any exceptions that occur during the process.
     */
    public function exceptionList(Request $request)
    {
        try {
            $prams = ['search' => $request->search ?? null, 'status' => $request->status ?? 'all', 'row' => $request->perPage];
            $list = $this->_exceptionList(...$prams);
            return $this->actionSuccess('Exception List Get successfully', $list);
        } catch (\Exception $e) {
            createExceptionError($e, EXCEPTION_CONTROLLER, __FUNCTION__ );
            return $this->actionFailure($e->getMessage());
        }
    }

    /**
     * The function _exceptionList retrieves paginated exception logs based on search criteria and status.
     *
     * @param string search The `search` parameter is used to filter the results based on a search term. If
     * provided, the function will search for the term in the `title`, `error`, and `file_name` columns of
     * the `ExceptionLog` table using the `like` operator.
     * @param string status The `_exceptionList` function accepts three parameters:
     * @param int row The `row` parameter in the `_exceptionList` function is used to specify the number of
     * results per page when paginating the query results. In this case, the default value for `row` is set
     * to 50, meaning that by default, the function will return 50 results per page
     */
    public function _exceptionList(string $search = null, string $status = 'all', int $row = 50)
    {
        $query = ExceptionLog::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")->orWhere('error', 'like', "%$search%")->orWhere('file_name', 'like', "%$search%");
            });
        }

        if ($status != 'all') {
            $query->where(function ($q) use ($status) {
                $q->where('status', 'like', "%$status%");
            });
        }

        return $query->latest()->paginate($row);
    }

    /**
     * The function `exceptionStatusUpdate` updates the status of an exception log in a database
     * transaction, handling validation errors and exceptions.
     */
    public function exceptionStatusUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exception_id' => 'required|uuid',
            'status' => 'required|string|in:Pending,Complete,Cancel',
            'comment' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        DB::beginTransaction();
        try {
            ExceptionLog::where('id', $request->exception_id)->update(['status' => $request->status, 'comment' => $request->comment]);
            $exception_count = ExceptionLog::where('status', ExceptionLog::PENDING)->count();
            DB::commit();
            return $this->actionSuccess('Exception status updated successfully.', $exception_count);
        } catch (\Exception $e) {
            DB::rollBack();
            createExceptionError($e, EXCEPTION_CONTROLLER, __FUNCTION__);
            return $this->actionFailure('Failed to update exception status: ' . $e->getMessage());
        }
    }

    /**
     * The function `exceptionDeleteInfo` handles the deletion of an exception log with error handling and
     * database transactions in a PHP Laravel application.
     */
    public function exceptionDeleteInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exception_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return $this->actionFailure($validator->errors()->first());
        }
        DB::beginTransaction();
        try {
            ExceptionLog::where('id', $request->exception_id)->delete();
            $exception_count = ExceptionLog::where('status', ExceptionLog::PENDING)->count();
            DB::commit();
            return $this->actionSuccess('Exception log deleted successfully.', $exception_count);
        } catch (\Exception $e) {
            DB::rollBack();
            createExceptionError($e, EXCEPTION_CONTROLLER, __FUNCTION__);
            return $this->actionFailure('Failed to delete exception log: ' . $e->getMessage());
        }
    }
}
