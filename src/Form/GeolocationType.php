<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Form;
    
    use Eckinox\TinymceBundle\Form\Type\TinymceType;
    use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
    use Symfony\Component\Form\Extension\Core\Type\DateType;
    use Symfony\Component\Form\Extension\Core\Type\EnumType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    
    class GeolocationType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('token', null, array(
                    'disabled' => true,
                    "attr" => array(
                        "placeholder" => "tokyo.form.token.placeholder",
                        'class' => 'required email sm-form-control border-form-control'
                    ),
                    'label' => 'tokyo.form.token.label',
                    'label_attr' => ['class' => 'col-md-12'],
                    'translation_domain' => 'tokyo',
                    'required' => true,
//                    'label_attr' => ['class' => 'required email sm-form-control border-form-control'],
                ))
                ->add('expire', DateTimeType::class, array(
                    'input' => 'datetime_immutable',
                    'widget' => 'single_text',
                    "attr" => array(
                        'autocomplete' => "off",
                        'inputmode' => 'none',
                        'data-toggle' => 'datetimepicker',
                        'data-target' => '.datetimepicker3',
                        "placeholder" => "tokyo.form.view.placeholder",
                        'class' => 'datetimepicker-input datetimepicker3 text-start'
                    ),
                    'label' => 'tokyo.form.view.label',
                    'label_attr' => ['class' => 'text-uppercase ls-3 fw-bold col-md-12'],
                    'translation_domain' => 'tokyo',
//                'required' => true,
//                    'label_attr' => ['class' => 'required email sm-form-control border-form-control'],
                ))
                ->add('name', null, array(
//                    'disabled'      => true,
                    "attr" => array(
                        "placeholder" => "tokyo.form.name.placeholder",
                        'class' => 'required email sm-form-control border-form-control'
                    ),
                    'label' => 'tokyo.form.name.label',
                    'label_attr' => ['class' => 'col-md-12'],
                    'translation_domain' => 'tokyo',
                    'required' => true,
//                    'label_attr' => ['class' => 'required email sm-form-control border-form-control'],
                ))
                ->add('type', EnumType::class, array(
                    'class' => TokyoEnum::class,
                    'expanded' => false,
                    'choice_label' => fn($choice) => match ($choice) {
                        TokyoEnum::TOKYO => 'enum.tokyo.tokyo',
                        TokyoEnum::LIMA => 'enum.tokyo.lima',
                    },
                    "required" => true,
                    'choice_translation_domain' => 'tokyo',
                    "attr" => array(
                        'class' => 'required sm-form-control border-form-control',
                        "placeholder" => "tokyo.form.type.placeholder",
                    ),
                    'label' => 'tokyo.form.type.label',
                    'label_attr' => ['class' => 'col-md-12'],
                    'translation_domain' => 'tokyo'
                ))
                ->add('company', null, array(
//                    'disabled'      => true,
                    "attr" => array(
                        "placeholder" => "tokyo.form.company.placeholder",
                        'class' => 'required email sm-form-control border-form-control'
                    ),
                    'label' => 'tokyo.form.company.label',
                    'label_attr' => ['class' => 'col-md-12'],
                    'translation_domain' => 'tokyo',
                    'required' => true,
//                    'label_attr' => ['class' => 'required email sm-form-control border-form-control'],
                ))
                ->add('phoneNumber', PhoneNumberType::class, array(
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                    'country_choices' => ['FR', 'CH'],
                    'preferred_country_choices' => ['FR'],
                    "attr" => array(
                        "placeholder" => "tokyo.form.phoneNumber.placeholder",
                        'class' => 'col-md-12 required'
                    ),
                    
                    'label' => 'tokyo.form.phoneNumber.label',
                    'label_attr' => ['class' => 'text-uppercase ls-3 fw-bold col-md-6'],
                    'translation_domain' => 'tokyo',
                    'required' => false,
//                    'label_attr' => ['class' => 'required email sm-form-control border-form-control'],
                ))
                ->add('email', null, array(
//                    'disabled'      => true,
                    "attr" => array(
                        "placeholder" => "tokyo.form.email.placeholder",
                        'class' => 'required email sm-form-control border-form-control'
                    ),
                    'label' => 'tokyo.form.email.label',
                    'label_attr' => ['class' => 'col-md-12'],
                    'translation_domain' => 'tokyo',
                    'required' => false,
//                    'label_attr' => ['class' => 'required email sm-form-control border-form-control'],
                ))
                ->add('message', TinymceType::class, [
                    "attr" => [
                        "placeholder" => 'Neox ....',
                        "selector" => 'post_summary',
//                    "language_url" => '/bundles/tinymce/ext/tinymce/langs/fr_FR.js', // path from the root of your web application — / — to the language pack(s)
                        "language" => 'fr_FR',
                        "skin" => "oxide",
                        //TableTools
                        "plugins" => 'code image link lists fullscreen table',
                        "toolbar" => 'undo redo | table | styles | fontfamily fontsize forecolor backcolor | link image code fullscreen | align | bullist numlist', "menubar" => true
                    ],
                ])
//            ->add('createdAt')
//            ->add('updatedAt')
            ;
        }
        
        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => Tokyo::class,
            ]);
        }
    }
