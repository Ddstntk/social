<?php
/**
 * Pswd type.
 *
/**
 * Access type.
 *
 * @category  Social_Network
 * @package   Social
 * @author    Konrad Szewczuk <konrad3szewczuk@gmail.com>
 * @copyright 2018 Konrad Szewczuk
 * @license   https://opensource.org/licenses/MIT MIT license
 * @link      cis.wzks.uj.edu.pl/~16_szewczuk
 */
namespace Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Validator\Constraints as CustomAssert;


/**
 * Class PswdType
 *
 * @category  Social_Network
 * @package   Form
 * @author    Konrad Szewczuk <konrad3szewczuk@gmail.com>
 * @copyright 2018 Konrad Szewczuk
 * @license   https://opensource.org/licenses/MIT MIT license
 * @link      cis.wzks.uj.edu.pl/~16_szewczuk
 */
class PswdType extends AbstractType
{
    /**
     * Form builder
     *
     * @param FormBuilderInterface $builder Form builder
     * @param array                $options Form options
     *
     * @return none
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'password',
            RepeatedType::class,
            [
                'type' => PasswordType::class,
                'first_options'  => array('label' => 'label.password'),
                'second_options' => array('label' => 'label.repeat.password'),
                'invalid_message' => 'label.password.verify',
                'options' => array('attr' => array('class' => 'password-field')),
                'label' => 'label.password',
                'required' => true,
                'attr' => [
                    'max_length' => 32,

                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            'min' => 8,
                            'max' => 32,
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Options configuration
     *
     * @param OptionsResolver $resolver Options Resolver
     *
     * @return none
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => 'pswd-default',
                'user_repository' => null,
            ]
        );
    }

    /**
     * Get prefix
     *
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return 'pswd_type';
    }
}
