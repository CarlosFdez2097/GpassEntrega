<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use \Firebase\JWT\JWT;

class CategoryController extends Controller
{
    public function index()
    {
        if ($this->checkLogin()) 
        { 
            $userData = $this->getUserData();
            $categoriesSave = $this->allCategoriesOneUser($userData->id);

            if(count($categoriesSave) < 1 )
            {
                return $this->error(400, "No has creado categorias");
            }

            return $this->success('Todas las categorias creadas por el usuario', $categoriesSave);
        }
        else
        {
            return $this->error(400, "No tienes permisos");
        }    
    }
   
    public function create()
    {
        
    }
    
    public function store(Request $request)
    {
        if ($this->checkLogin()) 
        { 
            if(!$request->filled("categoryName"))
            {
                return $this->error(400, "No puede estar vacio el nombre de la categoria");
            }

            $userData = $this->getUserData();

            if($this->isUsedCategoryName($request->categoryName,$userData->id))
            {
                return $this->error(400,'El nombre de la categoria ya esta siendo usado');
            }
            
            $category = new Category();
            $category->name = $this->deleteAllSpace($request->categoryName);
            $category->id_user = $userData->id;
            $category->save();
            return $this->success('Categoria creada', $request->categoryName);
        }
        else
        {
            return $this->error(400, "No tienes permisos");
        }    
    }

    public function show($categoryName)
    {
        if ($this->checkLogin()) 
        {
            if(is_null($categoryName))
            {
                return $this->error(400, "El nombre de la categoria tiene que estar rellenado");
            }

            $userData = $this->getUserData();

            $categorySave = $this->oneCategoryOfUser($userData->id,$categoryName);

            if(count($categoriesSave) >= 1)
            {
                return $this->error(400, "No ha creado esa categoria");
            }

            return $this->success('La categoria selecionada', $categorySave);
        }
        else
        {
            return $this->error(400, "No tienes permisos");
        } 
    }
   
    public function edit($categoryname)
    {
           
    }

    public function update(Request $request, $category)
    {
        if ($this->checkLogin()) 
        { 
            if(is_null($category))
            {
                return $this->error(400, "El nombre de la categoria tiene que estar rellenado");
            }

            if(!$request->filled("newCategoryName"))
            {
                return $this->error(400, "El nombre de la nueva categoria tiene que estar rellenado");
            }

            if(is_null($category))
            {
                return $this->error(400, "El nombre de la categoria que quieres cambiar debe estar rellenado");
            }

            $newName = $request->newCategoryName;
            $userData = $this->getUserData();

            if($this->isUsedCategoryName($newName,$userData->id))
            {
                return $this->error(400,'El nuevo nombre de la categoria ya esta siendo usado');
            }

            $categoryName = $category;

            if(is_null($category))
            {
                return $this->error(400, "La categoria no se a encontrado");
            }

            $categorySave = $this->oneCategoryOfUser($userData->id,$category);

            $categorySave->name = $newName;
            $categorySave->save();

            return $this->success('La categoria a sido actualizada', $categorySave);
        }
        else
        {
            return $this->error(400, "No tienes permisos");
        }
    }
   
    public function destroy($category)
    {
        if ($this->checkLogin()) 
        { 
            $categoryName = $category;
            $categorySave = Category::where('name',$categoryName)->first();
            $categorySave->delete();
            return $this->success('ha sido borrada la categoria', "");
        }
        else
        {
            return $this->error(400, "No tienes permisos");
        }       
    }

    private function isUsedCategoryName($categoryName,$id_user)
    {
        $categoriesSave = $this->allCategoriesOneUser($id_user);

        foreach ($categoriesSave as $Category => $CategorySave) 
        {
            if($CategorySave->name == $categoryName)
            {
                return true;
            }  
        }
        return false;
    }

    private function allCategoriesOneUser($id)
    {
        return Category::where('id_user', $id)->get();
    }

    private function oneCategoryOfUser($id,$categoryname)
    {
        $categoriesSave = $this->allCategoriesOneUser($id);

        foreach ($categoriesSave as $categories => $categorie)
        {
            if($categoryname == $categorie->name)
            {
                return $categorie;
            }
        }
        return null;
    }
}
