<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Boletos;
use Illuminate\Support\Facades\Log;

class BoletosController extends Controller
{
    public function uploadCsv(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $file = $request->file('file');
        $filePath = $file->getRealPath();
        $file = fopen($filePath, 'r');
        $header = fgetcsv($file);

        while ($row = fgetcsv($file)) {
            $data = array_combine($header, $row);

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'governmentId' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'debtAmount' => 'required|numeric',
                'debtDueDate' => 'required|date',
                'debtId' => 'required|unique:boletos|uuid',
            ]);

            if (!$validator->fails()) {
               
                Boletos::create([
                    'name' => $data['name'],
                    'governmentId' => $data['governmentId'],
                    'email' => $data['email'],
                    'debtAmount' => $data['debtAmount'],
                    'debtDueDate' => $data['debtDueDate'],
                    'debtId' => $data['debtId'],
                ]);
            }

           /* echo $data['name'].",". $data['governmentId'].",".$data['email'].",".
            $data['debtAmount'].",".$data['debtDueDate'].",". $data['debtId']."\n";*/

            
        }

        fclose($file);

        return response()->json(['success' => 'Arquivo importado com sucesso!'], 200);
    }

    public function generateBoleto(Request $request)
    {
        if(!$request || !$request->debtId){
            Log::warning('Parametro nao enviado');
            return response()->json('Parametro não encontrado.', 404);
        }

        $boleto = Boletos::where('debtId', $request->debtId)->first();

        if (!$boleto) {
            Log::warning('Tentativa de geração de boleto para débito inexistente', ['debtId' => $request->debtId]);
            return response()->json('Boleto não encontrado.', 404);
        }
        else if($boleto->status != 0 ) {
            Log::warning('Boleto já foi gerado anteriormente', ['debtId' => $request->debtId]);
            return response()->json('Boleto já foi gerado para ' . $boleto->name, 404);
        }

        Log::info('Boleto gerado:', [
            'name' => $boleto->name,
            'governmentId' => $boleto->governmentId,
            'email' => $boleto->email,
            'debtAmount' => $boleto->debtAmount,
            'debtDueDate' => $boleto->debtDueDate,
            'debtId' => $boleto->debtId,
        ]);

        $boleto->status = 1;
        $boleto->save();

        return response()->json('Boleto gerado com sucesso para ' . $boleto->name, 200);
        
    }

    public function generateEmail(Request $request)
    {
        if(!$request->debtId){
            Log::warning('Parametro nao enviado');
            return response()->json('Parametro não encontrado.', 404);
        }

        $boleto = Boletos::where('debtId', $request->debtId)->first();

        if (!$boleto) {
            Log::warning('Tentativa de envio de email para débito inexistente', ['debtId' => $request->debtId]);
            return response()->json('Boleto não encontrado.', 404);
        }
        else if($boleto->status == 0 ) {
            Log::warning('Boleto ainda nao foi gerado', ['debtId' => $request->debtId]);
            return response()->json('Boleto ainda nao foi gerado ' . $boleto->name, 404);
        }
        else if($boleto->status == 2 ) {
            Log::warning('Email já foi enviado anteriormente', ['debtId' => $request->debtId]);
            return response()->json('Email já foi enviado para ' . $boleto->name, 404);
        }

        Log::info('Email enviado:', [
            'name' => $boleto->name,
            'governmentId' => $boleto->governmentId,
            'email' => $boleto->email,
            'debtAmount' => $boleto->debtAmount,
            'debtDueDate' => $boleto->debtDueDate,
            'debtId' => $boleto->debtId,
        ]);

        $boleto->status = 2;
        $boleto->save();

        return response()->json('Email enviado com sucesso para ' . $boleto->name, 200);
        
    }

}
