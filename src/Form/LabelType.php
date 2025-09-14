<?php

namespace App\Form;

use App\Entity\Label;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class LabelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Label Name',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter label name'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Label name cannot be blank'
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Label name must be at least {{ limit }} characters long',
                        'maxMessage' => 'Label name cannot be longer than {{ limit }} characters'
                    ])
                ]
            ])
            ->add('prompt', TextareaType::class, [
                'label' => 'AI Prompt',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Enter the condition for when this label should be applied by AI'
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
            'data_class' => Label::class,
        ]);
    }
}
