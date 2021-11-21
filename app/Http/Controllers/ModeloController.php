<?php

namespace App\Http\Controllers;

use App\Models\Modelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ModeloController extends Controller
{   
    public function __construct(Modelo $modelo) {
        $this->modelo = $modelo;
    }

  
    public function index(Request $request)
    {      
        $modelos = array();

        if($request->has('atributos')) {
            $attributes = $request->atributos;
            $modelos = $this->modelo->selectRaw($attributes)->with('marca')->get();
        } else {
            $modelos = $this->modelo->with('marca')->get();
        }

        return response()->json($modelos, 200);
    }


    public function store(Request $request)
    {
        $request->validate($this->modelo->rules());

        $image = $request->file('imagem');
        $image_urn = $image->store('imagens/modelos', 'public');

        $modelo = $this->modelo->create([
            'marca_id'      => $request->marca_id,
            'nome'          => $request->nome,
            'imagem'        => $image_urn,
            'numero_portas' => $request->numero_portas,
            'lugares'       => $request->lugares,
            'air_bag'       => $request->air_bag,
            'abs'           => $request->abs
        ]);

        return response()->json($modelo, 201);
    }


    public function show($id)
    {
        $modelo = $this->modelo->with('marca')->find($id);
        if($modelo === null) {
            return response()->json(['error' => "This register dont't exists."], 404);
        }
        return response()->json($modelo, 200);
    }


    public function update(Request $request, $id)
    {   
        $modelo = $this->modelo->find($id);        

        if ($modelo === null) {
            return response([
                'error' => "This register dont't exists."
            ], 404);
        }

        if ($request->method() == 'PATCH'){

            $dinamicRules = array();

            foreach($modelo->rules() as $input => $rule) {
                if(array_key_exists($input, $request->all())) {
                    $dinamicRules[$input] = $rule;
                }
            }

            $request->validate($dinamicRules);

        } else {

            $request->validate($this->modelo->rules());
        }

        if($request->file('imagem')) {
            Storage::disk('public')->delete($modelo->imagem);
        }

        $image = $request->file('imagem');

        $image_urn = $image->store('imagens/modelos', 'public');

        $modelo->fill($request->all());
        $modelo->imagem = $image_urn;
        $modelo->save();

        return response()->json($modelo, 200);
    }


    public function destroy($id)
    {
        $modelo = $this->modelo->find($id);

        if ($modelo === null) {
            return response(["error" => "This record can't be deleted."], 404);
        }

        Storage::disk('public')->delete($modelo->imagem);
        $modelo->delete();
        return response(['msg' => 'A marca foi deletada com sucesso!']);
    }
}
