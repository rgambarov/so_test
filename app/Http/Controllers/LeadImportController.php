<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreviewLeadsRequest;
use App\Services\Imports\LeadSpreadsheetReader;
use App\Http\Requests\ImportLeadsRequest;
use App\Services\Imports\LeadImportService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use OpenSpout\Common\Exception\OpenSpoutException;

class LeadImportController extends Controller
{
    public function index(): View
    {
        return view('imports.index', ['preview' => null, 'count' => DB::table('leads')->count()]);
    }

    public function preview(PreviewLeadsRequest $request, LeadSpreadsheetReader $reader): View
    {
        try {
            $preview = $reader->preview(
                $request->file('file')->getRealPath(),
                config('import.preview_rows'),
            );
        } catch (InvalidArgumentException|OpenSpoutException $exception) {
            throw ValidationException::withMessages(['file' => $exception->getMessage()]);
        }

        return view('imports.index', ['preview' => $preview, 'count' => DB::table('leads')->count()]);
    }

    public function import(
        ImportLeadsRequest $request,
        LeadImportService $service,
    ): RedirectResponse {
        try {
            $result = $service->import(
                $request->file('file')->getRealPath()
            );
        } catch (InvalidArgumentException|OpenSpoutException $exception) {
            throw ValidationException::withMessages([
                'file' => $exception->getMessage(),
            ]);
        } catch (QueryException $exception) {
            // MySQL error 1062 indicates a duplicate unique key.
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                $details = $exception->errorInfo[2] ?? 'Duplicate key.';

                throw ValidationException::withMessages([
                    'file' => 'Import rolled back. '.$details,
                ])->redirectTo(route('imports.index'));
            }

            report($exception);

            throw ValidationException::withMessages([
                'file' => 'A database error occurred. No leads were imported. Check the application logs.',
            ]);
        }

        return redirect()
            ->route('imports.index')
            ->with('import_result', $result);
    }
}
