<?php
// src/Form/Type/TaskType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\SubTask;

class SubTaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
              'attr' => ['class' => 'form-control'],
              'label' => 'Name'
            ])
            ->add('description', TextareaType::class, [
              'attr' => ['class' => 'form-control']
            ])
            ->add('start', DateTimeType::class, [
              'attr' => ['class' => 'form-control'],
              'label' => 'Fecha Inicio'
            ])
            ->add('endtime', DateTimeType::class, [
              'attr' => ['class' => 'form-control'],
              'label' => 'Fecha Fin',
              'required' => false,
            ])
            //->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SubTask::class,
        ]);
    }

  }
