<?php

namespace App\Form;

use App\Entity\Products;
use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProductsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Product Name',
                'attr' => [
                    'placeholder' => 'Enter product name',
                    'class' => 'mb-5'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Product Description',
                'attr' => [
                    'placeholder' => 'Enter product description',
                    'class' => 'mb-5'
                ]
            ])
            ->add('price', NumberType::class, [
                'label' => 'Product Price',
                'attr' => [
                    'placeholder' => 'Enter product price',
                    'class' => 'mb-5'
                ],
            ])
            ->add('stock', NumberType::class, [
                'label' => 'Product Stock',
                'attr' => [
                    'placeholder' => 'Enter product stock',
                    'class' => 'mb-5'
                ],
            ])
            ->add('isValid', CheckboxType::class, [
                'label' => 'Est-ce un produit valide ?',
                'required' => false,
            ])
            // ->add('slug')
            ->add('category', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'name', // Ou 'slug', selon l'affichage souhaité
                'placeholder' => 'Choisir une catégorie',
                'required' => true,
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
        ]);
    }
}
