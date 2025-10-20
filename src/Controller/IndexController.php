<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\PropertySearch;
use App\Form\ArticleType;
use App\Form\PropertySearchType;
use Dom\Entity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Entity\CategorySearch;
use App\Form\CategorySearchType;
use App\Entity\PriceSearch;
use App\Form\PriceSearchType;

class IndexController extends AbstractController
{
    #[Route('/articles', name: 'articles')]
    public function home(EntityManagerInterface $entity_manager,Request $request)
    {
        $propertySerach = new PropertySearch();
        $form = $this->createForm(PropertySearchType::class,$propertySerach);
        $form->handleRequest($request);
        $articles= [];
        if($form->isSubmitted() && $form->isValid()) {
            $nom = $propertySerach->getNom();
            if ($nom!="")
            $articles= $entity_manager->getRepository(Article::class)->findBy(['Nom' => $nom] );
            else
            $articles= $entity_manager->getRepository(Article::class)->findAll();
        }
        return $this->render('articles/index.html.twig', ['articles' => $articles,'form'=> $form->createView()]);
    }
    #[Route('/article/save')]
    public function save(EntityManagerInterface $entity_manager)
    {
        $article = new Article();
        $article->setNom("Mirage");
        $article->setPrix(100);

        $entity_manager->persist($article);
        $entity_manager->flush();

        return new Response('Article enregisté avec id' . $article->getId());
    }
    #[Route('/article/new',name:'new_article',methods: ['GET', 'POST'])]
    public function new(Request $request,EntityManagerInterface $entity_manager){
        $article = new Article();
        $form = $this->createForm(ArticleType::class,$article);
        $form ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $article = $form->getData();

            $entity_manager->persist($article);
            $entity_manager->flush();
            return $this->redirectToRoute('articles');
        }
        return $this->render('articles/new.html.twig',['form'=> $form->createView()]);
    }
    #[Route('/article/{id}', name:'article_show')]
    public function show($id,EntityManagerInterface $entity_manager){
        $article = $entity_manager->getRepository(Article::class)->find($id);
        return $this->render('articles/show.html.twig',array('article'=> $article));
    }
    #[Route('/article/edit/{id}', name:'edit_article',methods:['GET','POST'])]
    public function edit(Request $request,$id,EntityManagerInterface $entity_manager){
        $article = new Article();
        $article = $entity_manager->getRepository(Article::class)->find($id);
        
        $form = $this->createForm(ArticleType::class,$article);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $entity_manager->flush();
            return $this->redirectToRoute('articles');
        }
        return $this->render('articles/edit.html.twig',['form'=> $form->createView()]);
    }
    #[Route('/article/delete/{id}', name:'delete_article',methods:['DELETE','GET'])]
    public function delete(Request $request,$id,EntityManagerInterface $entity_manager){
        $article = $entity_manager->getRepository(Article::class)->find($id);
        $entity_manager->remove($article);
        $entity_manager->flush();

        return $this->redirectToRoute('articles');
    }
    #[Route('category/newCat',name:'new_category',methods:['GET','POST'])]
    public function newCategory(Request $request,EntityManagerInterface $entity_manager){
        $category = new Category();
        $form = $this->createForm(Category::class,$category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $category = $form->getData();
            $entity_manager->persist($category);
            $entity_manager->flush();
        }
        return $this->render('articles/newCategory.html.twig',["form"=> $form->createView()]);
    }
    #[Route('/art_cat/',name:"article_par_cat",methods:['GET','POST'])]
    public function articlesParCategorie(Request $request,EntityManagerInterface $entity_manager){
        $categorySearch =new CategorySearch();
        $form =$this->createForm(CategorySearchType::class,$categorySearch);
        $form->handleRequest($request);
        $articles = [];
        if($form->isSubmitted() && $form->isValid()) {
            $category = $categorySearch->getCategory();
            if ($category!="")
                $articles= $category->getArticles();
            else
                $articles= $entity_manager->getRepository(Article::class)->findAll();
            }
            return $this->render('articles/articlesParCategorie.html.twig',['form' => $form->createView(),'articles' => $articles]);
    }
    #[Route("/art_prix",name:"article_par_prix",methods:['GET','POST'])]
    public function articlesParPrix(Request $request,EntityManagerInterface $entity_manager){
        $priceSearch = new PriceSearch();
        $form = $this->createform(PriceSearchType::class,$priceSearch);
        $form->handleRequest($request);
        $articles=[];
        if($form->isSubmitted() && $form->isValid()){
            $minPrice = $priceSearch->getMinPrice();
            $maxPrice = $priceSearch->getMaxPrice();
            $articles = $entity_manager->getRepository(Article::class)->findbyPriceRange($minPrice,$maxPrice);
        }
        return $this->render('articles/articlesParPrix.html.twig',['form'=>$form->createView(),'articles'=>$articles]);
    }
}