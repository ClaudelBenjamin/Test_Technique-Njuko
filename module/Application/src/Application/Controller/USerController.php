<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class UserController extends AbstractActionController
{
    public function listAction()
    {

        $users = $this->getServiceLocator()->get('entity_manager')
            ->getRepository('Application\Entity\User')
            ->findAll();

        return new ViewModel(array(
            'users' =>  $users
        ));
    }

	public function sortAction()
    {
        $rep = $this->getServiceLocator()->get('entity_manager')
            ->getRepository('Application\Entity\User');
			if ($this->params()->fromRoute('attribut')=='id' || $this->params()->fromRoute('attribut')=='email'){
				$users = $rep->findBy(array(),array($this->params()->fromRoute('attribut') => 'asc'));
			}else{
				$queryBuilder = $this->getServiceLocator()->get('entity_manager')->createQueryBuilder();
				$queryBuilder->select('u.id, u.email, p.lastName, p.firstName,p.address, p.birth_date')
					->from('Application\Entity\User', 'u')
					->from('Application\Entity\Profile', 'p')
					->where('u.profile = p.id')
					->orderby('p.'.$this->params()->fromRoute('attribut'));
				$u = $queryBuilder->getQuery()
					->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
				$users=array();
				$i=0;
				foreach($u as $tmp){
					$users[$i]=array('id'=>$tmp['id'],
								 'email'=>$tmp['email'],
								 'profile'=>array('lastname'=>$tmp['lastName'],
												  'firstname'=>$tmp['firstName'],
												  'address'=>$tmp['address'],
												  'birth_date'=>$tmp['birth_date'],
												 )
								);
					$i++;
				}
			}
		
        return new ViewModel(array(
            'users' =>  $users
        ));
    }
	
    public function addAction()
    {
        /* @var $form \Application\Form\UserForm */
        $form = $this->getServiceLocator()->get('formElementManager')->get('form.user');

        $data = $this->prg();

        if ($data instanceof \Zend\Http\PhpEnvironment\Response) {
            return $data;
        }

        if ($data != false) {
            $form->setData($data);
            if ($form->isValid()) {

                /* @var $user \Application\Entity\User */
                $user = $form->getData();

                /* @var $serviceUser \Application\Service\UserService */
                $serviceUser = $this->getServiceLocator()->get('application.service.user');

                $serviceUser->saveUser($user);

                $this->redirect()->toRoute('users');
            }
        }

        return new ViewModel(array(
            'form'  =>  $form
        ));
    }

    public function removeAction()
    {
        //To do : Do Remove User
		$user = $this->getServiceLocator()->get('entity_manager')
            ->getRepository('Application\Entity\User')
            ->find($this->params()->fromRoute('user_id'));
		
		$serviceUser = $this->getServiceLocator()->get('application.service.user');
		
		$serviceUser->removeUser($user);
		
        $this->redirect()->toRoute('users');
    }

    public function editAction()
    {
        /* @var $form \Application\Form\UserForm */
        $form = $this->getServiceLocator()->get('formElementManager')->get('form.user');

        $userToEdit = $this->getServiceLocator()->get('entity_manager')
            ->getRepository('Application\Entity\User')
            ->find($this->params()->fromRoute('user_id'));

        $form->bind($userToEdit);
        $form->get('firstname')->setValue($userToEdit->getFirstname());
		$form->get('lastname')->setValue($userToEdit->getLastname());
        $form->get('address')->setValue($userToEdit->getAddress());
		$form->get('birth_date')->setValue($userToEdit->getBirth_Date());
		
		$data = $this->prg();

        if ($data instanceof \Zend\Http\PhpEnvironment\Response) {
            return $data;
        }

        if ($data != false) {
            $form->setData($data);
            if ($form->isValid()) {

                /* @var $user \Application\Entity\User */
                $user = $form->getData();

                //Save the user
				/* @var $serviceUser \Application\Service\UserService */
                $serviceUser = $this->getServiceLocator()->get('application.service.user');

                $serviceUser->saveUser($user);
				
                $this->redirect()->toRoute('users');
            }
        }

        return new ViewModel(array(
            'form'  =>  $form
        ));
    }

}