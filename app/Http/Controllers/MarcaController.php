<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{

    public function __construct(Marca $marca) {
        $this->marca = $marca;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        //$marcas = Marca::all();
        $marcas = $this->marca->all();
        return response()->json($marcas, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   

        /** Tratamento e validação dos parametros */
        $request->validate($this->marca->rules(), $this->marca->feedback());

        /** dd($request->get('nome')); */
        /** dd($request->file('imagem')); */

        $image = $request->file('imagem');

        /**
         * O método store recebe dois parametros para armazenar a imagem
         * store(<PATH>, <DISCO>)
         * O DISCO é configurado em config/filesystems.php 
         * Existem 3 tipos: local, public e cloud (AWS S3)
         * Por padrão o disco é setado para local
         */

        $image->store('imagens', 'public');

        dd('Upload de arquivos');

        //$marca = $this->marca->create($request->all());
        return response()->json($marca, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {   
        $marca = $this->marca->find($id);
        if($marca === null) {
            return response()->json([
                'error' => "This register dont't exists."
            ], 404);
        }
        return response()->json($marca, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {   
        // $marcas = Marca::find($marca->id);
        $marca = $this->marca->find($id);
        
        if ($marca === null) {
            return response([
                'error' => "This register dont't exists."
            ], 404);
        }

        if ($request->method() == 'PATCH'){

            $dinamicRules = array();


            foreach($marca->rules() as $input => $rule) {

                /** Coleta apenas as regras parciais da requisição PATCH */
                if(array_key_exists($input, $request->all())) {
                    $dinamicRules[$input] = $rule;
                }
            }

            $request->validate($dinamicRules);

        } else {

            $request->validate($this->marca->rules(), $this->marca->feedback());
        }

        $marca->update($request->all());
        return response()->json($marca, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {   
        $marca = $this->marca->find($id);
        if ($marca === null) {
            return response([
                "error" => "This record can't be deleted."
            ], 404);
        }

        $marca->delete();
        return [
            'msg' => 'A marca foi deletada com sucesso!'
        ];
    }
}
 