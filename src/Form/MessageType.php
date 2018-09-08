<?php
/**
 * Message type.
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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MessageType.
 */
class MessageType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(

            'content', null, array(
                'label' => false,
                'attr' => array(
            //                'style' => 'height: 65%; width: 100%;'
                'rows'=> 5,
                ),
            ),
            TextareaType::class,
            [
                'required' => true,
                'attr' => array(
            //                    'style' => 'height: 10%; width: 100%;'
                ),
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['message-default']]
                    ),
                    new Assert\Length(
                        [
                            'groups' => ['message-default'],
                            'min' => 1,
                            'max' => 1000,
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => 'message-default',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'message_type';
    }
}
