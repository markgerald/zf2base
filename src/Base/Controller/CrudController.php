<?php

namespace Base\Controller;

use Zend\Mvc\Controller\AbstractActionController,
    Zend\View\Model\ViewModel;


/**
 * Class CrudController
 * @package Base\Controller
 */
abstract class CrudController extends AbstractActionController
{

    /**
     * @var
     */
    protected $em;
    /**
     * @var
     */
    protected $service;
    /**
     * @var
     */
    protected $entity;
    /**
     * @var
     */
    protected $form;
    /**
     * @var
     */
    protected $route;
    /**
     * @var
     */
    protected $controller;
    /**
     * @var
     */
    protected $permission;
    /**
     * @var
     */
    protected $namespaceMessage;
    /**
     * @var
     */
    protected $successNew;
    /**
     * @var
     */
    protected $successEdit;
    /**
     * @var
     */
    protected $errorNew;
    /**
     * @var
     */
    protected $errorEdit;

    /**
     * @return ViewModel
     */
    public function indexAction() {
        $list = $this->getEm()
                ->getRepository($this->entity)
                ->findAll();

        $successMessages = $this->flashMessenger()
                ->setNamespace($this->namespaceMessage)
                ->getSuccessMessages();

        $errorMessages = $this->flashMessenger()
                ->setNamespace($this->namespaceMessage)
                ->getErrorMessages();

        return new ViewModel(array('data' => $list, 'successMessages' => $successMessages, 'errorMessages' => $errorMessages));
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function newAction() {
        $entity = new $this->entity;

        $form = new $this->form($this->getEm());
        $request = $this->getRequest();
        $form->bind($entity);

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                if ($service->insert($entity)) {
                    $this->flashMessenger()
                            ->setNamespace($this->namespaceMessage)
                            ->addSuccessMessage($this->successNew);
                } else {
                    $this->flashMessenger()
                            ->setNamespace($this->namespaceMessage)
                            ->addErrorMessage($this->errorNew);
                }

                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
            }
        }

        return new ViewModel(array('form' => $form));
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function editAction() {
        $form = new $this->form($this->getEm());
        $request = $this->getRequest();

        $repository = $this->getEm()->getRepository($this->entity);
        $entity = $repository->find($this->params()->fromRoute('id', 0));

        if (!is_null($entity))
            $form->bind($entity);

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                if ($service->update($entity) == null) {
                    $this->flashMessenger()
                            ->setNamespace($this->namespaceMessage)
                            ->addSuccessMessage($this->successEdit);
                } else {
                    $this->flashMessenger()
                            ->setNamespace($this->namespaceMessage)
                            ->addErrorMessage($this->errorEdit);
                }

                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
            }
        }

        return new ViewModel(array('form' => $form));
    }

    /**
     * @return \Zend\Http\Response
     */
    public function deleteAction() {
        $id = $this->params()->fromRoute('id', 0);
        $entity = $this->getEm()->getReference($this->entity, $id);
        if ($entity) {
            $this->em->remove($entity);
            $this->em->flush();

            $this->flashMessenger()
                    ->setNamespace('Admin')
                    ->addSuccessMessage("Registro excluído com sucesso");

            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
        }
    }

    /**
     * 
     * @return EntityManager
     */
    protected function getEm()
    {
        if (null === $this->em)
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        return $this->em;
    }

}
