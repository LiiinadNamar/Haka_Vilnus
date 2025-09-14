<?php

namespace App\Form;

use App\Entity\Priority;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PriorityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', IntegerType::class, [
                'label' => 'Priority Number',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter priority number (1-10)',
                    'min' => 1,
                    'max' => 10
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Priority number cannot be blank'
                    ]),
                    new Assert\Range([
                        'min' => 1,
                        'max' => 10,
                        'notInRangeMessage' => 'Priority number must be between {{ min }} and {{ max }}'
                    ])
                ]
            ])
            ->add('prompt', TextareaType::class, [
                'label' => 'AI Prompt',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Enter the condition for when this priority should be assigned by AI'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'AI prompt cannot be blank'
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'max' => 500,
                        'minMessage' => 'AI prompt must be at least {{ limit }} characters long',
                        'maxMessage' => 'AI prompt cannot be longer than {{ limit }} characters'
                    ])
                ]
            ])
            ->add('color', ColorType::class, [
                'label' => 'Color',
                'attr' => [
                    'class' => 'form-control form-control-color'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Color cannot be blank'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Priority::class,
        ]);
    }
}
