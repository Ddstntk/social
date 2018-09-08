<?php
/**
 * Photos controller.
 *
 * @author    Konrad Szewczuk
 * @copyright (c) 2018 Konrad Szewczuk
 * @category  Social Media
 * @link      cis.wzks.uj.edu.pl/~16_szewczuk
 *
 * Collage project - social network
 */
namespace Controller;

use Form\PhotoType;
use Repository\PhotosRepository;
use Service\FileUploader;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PhotosController.
 */
class PhotosController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        //        $controller->get('/add', [$this, 'addAction'])->bind('photo_add');
        $controller->match('/add', [$this, 'addAction'])
            ->method('POST|GET')
            ->bind('photo_add');
        //        $controller->get('/add', [$this, 'addAction'])->bind('photo_add');


        return $controller;
    }
    /**
     * Add action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function addAction(Application $app, Request $request)
    {
        $photo = [];

        $form = $app['form.factory']->createBuilder(PhotoType::class, $photo)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userId = $app['security.token_storage']->getToken()->getUser()->getID();

            $photo  = $form->getData();
            $fileUploader = new FileUploader($app['config.photos_directory']);
            $fileName = $fileUploader->upload($photo['photo']);
            $photo['photo']=$fileName;
            $photosRepository = new PhotosRepository($app['db']);
            $photosRepository->save($photo, $userId);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type'    => 'success',
                    'message' => 'message.element_successfully_added',
                ]
            );

            //            return $app->redirect(
            //                $app['url_generator']->generate('user_profile'),
            //                301
            //            );
            var_dump($fileUploader);
        }

        return $app['twig']->render(
            'photo/add.html.twig',
            [
                'photo'  => $photo,
                'form' => $form->createView(),
            ]
        );
    }
    // ...
}