<?php
/**
 * Created by PhpStorm.
 * User: user1
 * Date: 31.10.16
 * Time: 15:07
 */

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use AppBundle\Entity\BlogPost;


class BlogPostAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title', 'text')
            ->add('body', 'textarea')
            ->add('img', 'file', array('label' => 'Картинка', 'required'=>false, 'data_class'=>null))
            ->add('updated_at', 'datetime')
            ->add('tags', 'entity', array('class' => 'AppBundle\Entity\BlogTag', 'choice_label' => 'name',))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
        ->addIdentifier('title')
        ->add('body')
        ->add('img')
        ->add('tags', 'entity', array('class' => 'AppBundle\Entity\BlogTag', 'associated_property' => 'name',))
        ->add('updated_up')
    ;
    }

    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('title')
            ->add('body')
            ->add('img')

        ;
    }

    public function toString($object)
    {
        return $object instanceof BlogPost
            ? $object->getTitle()
            : 'Blog Post'; // shown in the breadcrumb on the create view
    }
}