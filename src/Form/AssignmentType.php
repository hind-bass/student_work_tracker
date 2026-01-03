<?php
namespace App\Form;

use App\Entity\Assignment;
use App\Entity\Course;
use App\Enum\AssignmentPriority;
use App\Enum\AssignmentStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;

/**
* Formulaire pour créer/modifier un travail
* Formulaire complet avec tous les champs
*/
class AssignmentType extends AbstractType
{
public function __construct(
private Security $security
) {}

public function buildForm(FormBuilderInterface $builder, array $options): void
{
$user = $this->security->getUser();

$builder
->add('title', TextType::class, [
'label' => 'Titre du travail',
'attr' => [
'class' => 'form-control form-control-lg',
'placeholder' => 'Ex: Devoir de mathématiques chapitre 5',
'autofocus' => true
],
'constraints' => [
new NotBlank([
'message' => 'Le titre est obligatoire'
]),
new Length([
'min' => 3,
'max' => 255,
'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères',
'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères'
])
],
'help' => 'Titre descriptif du travail à réaliser'
])
->add('description', TextareaType::class, [
'label' => 'Description détaillée',
'required' => false,
'attr' => [
'class' => 'form-control',
'rows' => 4,
'placeholder' => 'Décrivez en détail le travail à réaliser, les consignes, les ressources nécessaires...'
],
'constraints' => [
new Length([
'max' => 5000,
'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
])
],
'help' => 'Description complète avec consignes et détails'
])
->add('course', EntityType::class, [
'class' => Course::class,
'label' => 'Matière',
'attr' => ['class' => 'form-select'],
'choice_label' => function(Course $course) {
return $course->getName() . ' (' . $course->getCode() . ')';
},
'query_builder' => function($repository) use ($user) {
return $repository->createQueryBuilder('c')
->andWhere('c.user = :user')
->setParameter('user', $user)
->orderBy('c.name', 'ASC');
},
'placeholder' => '-- Sélectionnez une matière --',
'constraints' => [
new NotNull([
'message' => 'La matière est obligatoire'
])
],
'help' => 'Matière à laquelle appartient ce travail'
])
->add('dueDate', DateTimeType::class, [
'label' => 'Date et heure d\'échéance',
'widget' => 'single_text',
'attr' => [
'class' => 'form-control',
'min' => (new \DateTime())->format('Y-m-d\TH:i')
],
'constraints' => [
new NotBlank([
'message' => 'La date d\'échéance est obligatoire'
]),
new GreaterThan([
'value' => 'today',
'message' => 'La date d\'échéance doit être dans le futur'
])
],
'help' => 'Date et heure limite de rendu'
])
->add('priority', EnumType::class, [
'class' => AssignmentPriority::class,
'label' => 'Niveau de priorité',
'attr' => ['class' => 'form-select'],
'choice_label' => function(AssignmentPriority $priority) {
return $priority->getLabel();
},
'constraints' => [
new NotNull([
'message' => 'La priorité est obligatoire'
])
],
'help' => 'Importance relative du travail'
])
->add('status', EnumType::class, [
'class' => AssignmentStatus::class,
'label' => 'Statut actuel',
'attr' => ['class' => 'form-select'],
'choice_label' => function(AssignmentStatus $status) {
return $status->getIcon() . ' ' . $status->getLabel();
},
'help' => 'État d\'avancement du travail'
])
->add('completionPercentage', RangeType::class, [
'label' => 'Pourcentage de complétion',
'attr' => [
'class' => 'form-range',
'min' => 0,
'max' => 100,
'step' => 5,
'oninput' => 'this.nextElementSibling.value = this.value + "%"'
],
'constraints' => [
new Range([
'min' => 0,
'max' => 100,
'notInRangeMessage' => 'Le pourcentage doit être entre {{ min }} et {{ max }}'
])
],
'help' => 'Utilisez le slider pour indiquer votre progression (0-100%)'
])
->add('estimatedHours', NumberType::class, [
'label' => 'Heures estimées',
'required' => false,
'attr' => [
'class' => 'form-control',
'placeholder' => '8.5',
'step' => '0.5',
'min' => '0'
],
'constraints' => [
new Positive([
'message' => 'Le nombre d\'heures doit être positif'
])
],
'help' => 'Estimation du temps nécessaire (en heures)'
])
->add('actualHours', NumberType::class, [
'label' => 'Heures réellement passées',
'required' => false,
'attr' => [
'class' => 'form-control',
'placeholder' => '10',
'step' => '0.5',
'min' => '0'
],
'constraints' => [
new Positive([
'message' => 'Le nombre d\'heures doit être positif'
])
],
'help' => 'Temps réellement passé sur ce travail (en heures)'
])
->add('notes', TextareaType::class, [
'label' => 'Notes personnelles',
'required' => false,
'attr' => [
'class' => 'form-control',
'rows' => 3,
'placeholder' => 'Ajoutez vos notes, ressources utiles, liens, rappels...'
],
'help' => 'Notes, liens, ressources ou remarques personnelles'
]);
}

public function configureOptions(OptionsResolver $resolver): void
{
$resolver->setDefaults([
'data_class' => Assignment::class,
]);
}
}
