<?php

namespace App\Form;

use App\Document\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                "attr" => [
                    'placeholder' => 'Enter your email'
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'label' => 'Password',
                'required' => true,
                'invalid_message' => 'The password fields must match.',
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Confirm Password'],
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Sign In',
                "attr" => [
                    'class' => 'bg-blue-500 text-white px-4 py-2 rounded-lg mt-4'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}