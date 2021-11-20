<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $image_urn = $image->store('imagens', 'public');

        $marca = $this->marca->create([
            'nome' => $request->nome,
            'imagem' => $image_urn
        ]);
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

        /**
         * Ao atualizar um registro, é necessário que a imagem anterior seja também
         * deletada, para isso é necessário seguir o seguite código
         */

        if($request->file('imagem')) {
            Storage::disk('public')->delete($marca->imagem);
        }

        $image = $request->file('imagem');

        $image_urn = $image->store('imagens', 'public');

        $marca->update([
            'nome' => $request->nome,
            'imagem' => $image_urn
        ]);

        /** Para atualização de imagens, é necessário adicionar o campo _method = PUT ou PATH
         *  no front (Body do Insomnia), pois o Laravel é limitado para esse  tipo de atualização
         */

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

        /**
         * Excluia a imagem
         */
        Storage::disk('public')->delete($marca->imagem);

        $marca->delete();
        return [
            'msg' => 'A marca foi deletada com sucesso!'
        ];
    }
}
 