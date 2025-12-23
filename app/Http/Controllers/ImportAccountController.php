<?php
namespace App\Http\Controllers;

use App\Imports\AccountImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportAccountController extends Controller
{
    public function showForm()
    {
        return view('import.form');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new AccountImport, $request->file('file'));
            return response()->json([
                'status' => true,
                'message' => 'import success',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => true,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
