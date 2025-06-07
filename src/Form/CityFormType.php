<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('city', TextType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank(message: 'Please enter a city name.'),
                ],
                'attr' => [
                    'placeholder' => 'Search for a city...',
                    'class' => 'w-full py-4 pl-12 pr-12 text-white transition-all duration-200 border bg-white/10 backdrop-blur-md border-white/20 rounded-2xl placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/40'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // No specific data_class needed for this simple form,
            // as getData() will return an array by default.
            // If you were binding to an object, you'd set 'data_class' here.
        ]);
    }
}
