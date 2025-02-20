<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\CitoyenRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

#[Route('/admin')]
class AdminController extends AbstractController
{
    private $userPasswordEncoder;
    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
    }
    #[Route('/', name: 'app_admin_index1', methods: ['GET'])]
    public function route(UserRepository $userRepository): Response
    {  $user=$this->getUser();
        $role=$user->getRoles();
        if (in_array("ROLE_ADMIN", $role)) 
            return $this->redirectToRoute('app_dashboard');
            if (in_array("ROLE_CITOYEN", $role)) 
              return $this->redirectToRoute('home');
              
        return $this->render('admin/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
    #[Route('/index', name: 'app_admin_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {  
              
        return $this->render('admin/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function dashboard(CitoyenRepository $userRepository): Response
    {  
              
        return $this->render('admin/dashboard.html.twig', [
            'citoyens' => $userRepository->findAll(),
        ]);
    }
    #[Route('/citoyenidex', name: 'app_citoyen_index', methods: ['GET'])]
    public function citoyen(CitoyenRepository $citoyenRepository): Response
    {
        return $this->render('citoyen/index.html.twig', [
            'citoyens' => $citoyenRepository->findAll(),
        ]);
    }
    #[Route('/new', name: 'app_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository): Response
    {
        $user = new Admin();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);//reccuperrer les données saisis

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success','Ajout avec Success');
            if ($user->getPassword()&&$user->getConfirmPassword()) {
                $user->setPassword(
                    $this->userPasswordEncoder->encodePassword($user, $user->getPassword())
                );
                $user->setConfirmPassword(
                    $this->userPasswordEncoder->encodePassword($user, $user->getConfirmPassword())
                );
                $user->eraseCredentials();
            }
            $roles[]='ROLE_ADMIN';
            $user->setRoles($roles);
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
