<?php
/**
 * Post type.
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PostType.
 */
class PostType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'content',
            TextType::class,
            [
                'label' => 'label.content',
                'required' => true,
                'attr' => [
                    'max_length' => 1000,
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['post-default']]
                    ),
                    new Assert\Length(
                        [
                            'groups' => ['post-default'],
                            'min' => 1,
                            'max' => 1000,
                        ]
                    ),
                ],
            ]
        )
            ->add(
                'visibility',
                ChoiceType::class, array(
                'label' => 'label.visibility',
                'choices'  => array(
                'Znajomi' => 0,
                'Wszyscy'=> 1,
                )
                //            'attr' => array(
                //                'style' => 'height: 300px;'
                //            ),
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => 'post-default',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'post_type';
    }
}
