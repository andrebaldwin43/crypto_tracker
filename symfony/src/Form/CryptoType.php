<?php

namespace App\Form;

use App\Document\Crypto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Document\User;

class CryptoType extends AbstractType
{
    public function __construct(
        #[Autowire('%app.crypto%')] 
        private array $cryptoChoices,
        private Security $security,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];
        foreach ($this->cryptoChoices as $key => $coin) {
            $choices[$coin['name']] = $key;
        }

        $builder
            ->add('crypto', ChoiceType::class, [
                'label' => 'Cryptocurrency',
                'choices' => $choices,
                'mapped' => false,
                'placeholder' => 'Choose a cryptocurrency',
                'constraints' => [new NotBlank()],
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Quantity',
                'constraints' => [new NotBlank()],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Cost',
                'constraints' => [new NotBlank()],
            ])
            ->add('purchaseDate', DateType::class, [
                'label' => 'Date Purchased',
                'constraints' => [new NotBlank()],
            ])
            ->add('submit', SubmitType::class);

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $crypto = $event->getData();
                $user   = $this->security->getUser();

                if (!$crypto instanceof Crypto || !$user instanceof User) {
                    throw new AccessDeniedException('A user account is required.');
                }

                $crypto->setUser($user);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Crypto::class,
        ]);
    }
}