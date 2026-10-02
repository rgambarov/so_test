<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreviewLeadsRequest;
use App\Services\Imports\LeadSpreadsheetReader;
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
}
