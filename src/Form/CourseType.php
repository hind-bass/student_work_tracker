<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

/**
* Formulaire pour créer/modifier une matière
* Avec validation complète
*/
class CourseType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options): void
{
$builder
->add('name', TextType::class, [
'label' => 'Nom de la matière',
'attr' => [
'class' => 'form-control form-control-lg',
'placeholder' => 'Ex: Mathématiques Avancées',
'autofocus' => true
],
'constraints' => [
new NotBlank([
'message' => 'Le nom de la matière est obligatoire'
]),
new Length([
'min' => 2,
'max' => 255,
'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
])
],
'help' => 'Nom complet de la matière'
])
->add('code', TextType::class, [
'label' => 'Code de la matière',
'attr' => [
'class' => 'form-control',
'placeholder' => 'Ex: MATH301',
'style' => 'text-transform: uppercase;'
],
'constraints' => [
new NotBlank([
'message' => 'Le code est obligatoire'
]),
new Length([
'min' => 2,
'max' => 50,
'minMessage' => 'Le code doit contenir au moins {{ limit }} caractères',
'maxMessage' => 'Le code ne peut pas dépasser {{ limit }} caractères'
]),
new Regex([
'pattern' => '/^[A-Z0-9-]+$/i',
'message' => 'Le code ne peut contenir que des lettres, chiffres et tirets'
])
],
'help' => 'Code unique pour identifier la matière (ex: MATH101, INFO202)'
])
->add('professor', TextType::class, [
'label' => 'Professeur',
'required' => false,
'attr' => [
'class' => 'form-control',
'placeholder' => 'Ex: Dr. Martin Dupont'
],
'constraints' => [
new Length([
'max' => 255,
'maxMessage' => 'Le nom du professeur ne peut pas dépasser {{ limit }} caractères'
])
],
'help' => 'Nom du professeur ou enseignant (optionnel)'
])
->add('color', ColorType::class, [
'label' => 'Couleur d\'identification',
'attr' => [
'class' => 'form-control form-control-color',
'style' => 'width: 100%; height: 50px; cursor: pointer;'
],
'constraints' => [
new NotBlank([
'message' => 'La couleur est obligatoire'
]),
new Regex([
'pattern' => '/^#[0-9A-Fa-f]{6}$/',
'message' => 'La couleur doit être au format hexadécimal (#RRGGBB)'
])
],
'help' => 'Choisissez une couleur pour identifier visuellement cette matière'
])
->add('description', TextareaType::class, [
'label' => 'Description',
'required' => false,
'attr' => [
'class' => 'form-control',
'rows' => 3,
'placeholder' => 'Décrivez brièvement le contenu de la matière...'
],
'constraints' => [
new Length([
'max' => 1000,
'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
])
],
'help' => 'Description optionnelle du contenu de la matière'
])
->add('credits', IntegerType::class, [
'label' => 'Nombre de crédits ECTS',
'required' => false,
'attr' => [
'class' => 'form-control',
'placeholder' => '6',
'min' => 1,
'max' => 30
],
'constraints' => [
new Range([
'min' => 1,
'max' => 30,
'notInRangeMessage' => 'Le nombre de crédits doit être entre {{ min }} et {{ max }}'
])
],
'help' => 'Nombre de crédits ECTS attribués à cette matière'
])
->add('semester', TextType::class, [
'label' => 'Semestre',
'required' => false,
'attr' => [
'class' => 'form-control',
'placeholder' => 'Ex: Automne 2024, S1, Premier Semestre'
],
'constraints' => [
new Length([
'max' => 50,
'maxMessage' => 'Le semestre ne peut pas dépasser {{ limit }} caractères'
])
],
'help' => 'Semestre ou période de la matière'
]);
}

public function configureOptions(OptionsResolver $resolver): void
{
$resolver->setDefaults([
'data_class' => Course::class,
]);
}
}
