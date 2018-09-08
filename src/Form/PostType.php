<?php
/**
 * Post type.
 *
 * @category  Social Media
 * @author    Konrad Szewczuk
 * @copyright (c) 2018 Konrad Szewczuk
 * @link      cis.wzks.uj.edu.pl/~16_szewczuk
 *
 * Collage project - social network
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
                'label.friends' => 0,
                'label.everyone'=> 1,
                )
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
