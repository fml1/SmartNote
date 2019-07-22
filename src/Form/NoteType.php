<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class NoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', TextType::class, [
                'disabled' => true,
                'empty_data' => '1',
                'label' => 'ID'
            ])
            ->add('tag', TextType::class, [
                'required' => false,
                'label' => 'Поисковые теги'
            ])
            ->add('attr_parent_id', TextType::class, [
                'required' => true,
                'empty_data' => '0',
                'label' => 'Родительский ID'
            ])
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Наименование заметки'
            ])
            ->add('attr_content', TextareaType::class, [
                'required' => false,
                'label' => false,
                'attr' => ['style' => 'display: none;']

            ])
            ->add('content', HiddenType::class, [
                'required' => false,
                'label' => false
            ])
            ->add('attr_order_id', HiddenType::class, [
                'required' => false,
                'label' => 'false'
            ])
            ->add('save', SubmitType::class, ['label' => 'Сохранить']);
    }

}
