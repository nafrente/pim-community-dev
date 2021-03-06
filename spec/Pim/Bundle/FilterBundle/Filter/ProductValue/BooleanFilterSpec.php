<?php

namespace spec\Pim\Bundle\FilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Prophecy\Argument;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class BooleanFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductFilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);
        $this->init('foo', [ProductFilterUtility::DATA_NAME_KEY => 'bar']);
    }

    function it_is_an_oro_boolean_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\BooleanFilter');
    }

    function it_initializes_filter_with_name()
    {
        $this->getName()->shouldReturn('foo');
    }

    function it_parses_data()
    {
        $this->parseData(['value' => 0])->shouldReturn(['value' => false]);
        $this->parseData(['value' => 1])->shouldReturn(['value' => true]);
        $this->parseData(['value' => true])->shouldReturn(false);
        $this->parseData(['value' => false])->shouldReturn(false);
        $this->parseData(null)->shouldReturn(false);
        $this->parseData([])->shouldReturn(false);
        $this->parseData(1)->shouldReturn(false);
        $this->parseData(0)->shouldReturn(false);
    }

    function it_applies_boolean_flexible_filter_on_the_datasource(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFilter($datasource, 'bar', '=', true)->shouldBeCalled();

        $this->apply($datasource, array('value' => BooleanFilterType::TYPE_YES))->shouldReturn(true);
    }

    function it_does_not_apply_boolean_flexible_filter_on_unparsable_data(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFilter(Argument::cetera())->shouldNotBeCalled();

        $this->apply($datasource, array('value' => 'foo'))->shouldReturn(false);
        $this->apply($datasource, array('value' => null))->shouldReturn(false);
        $this->apply($datasource, array())->shouldReturn(false);
        $this->apply($datasource, BooleanFilterType::TYPE_NO)->shouldReturn(false);
    }

    function it_uses_the_boolean_filter_form_type(FormInterface $form, $factory)
    {
        $factory->create(BooleanFilterType::NAME, [], ['csrf_protection' => false])->willReturn($form);

        $this->getForm()->shouldReturn($form);
    }

    function it_generates_choices_metadata(
        FormInterface $form,
        FormView $formView,
        FormView $fieldView,
        FormView $typeView,
        ChoiceView $yesChoice,
        ChoiceView $noChoice,
        ChoiceView $maybeChoice,
        $factory,
        $utility
    ) {
        $utility->getParamMap()->willReturn([]);
        $utility->getExcludeParams()->willReturn([]);
        $factory->create(BooleanFilterType::NAME, [], ['csrf_protection' => false])->willReturn($form);
        $form->createView()->willReturn($formView);

        $formView->children = array('value' => $fieldView, 'type' => $typeView);
        $formView->vars     = array('populate_default' => true);
        $fieldView->vars    = array('multiple' => true, 'choices' => array($yesChoice, $noChoice));
        $typeView->vars     = array('choices' => array($maybeChoice));

        $yesChoice->label = 'Yes';
        $yesChoice->value = 1;
        $noChoice->label  = 'No';
        $noChoice->value  = 0;

        $this->getMetadata()->shouldReturn(
            [
                'name'                 => 'foo',
                'label'                => 'Foo',
                'choices'              => [
                    [
                        'label' => 'Yes',
                        'value' => 1,
                    ],
                    [
                        'label' => 'No',
                        'value' => 0,
                    ]
                ],
                'enabled'              => true,
                'data_name'            => 'bar',
                'contextSearch'        => false,
                'populateDefault'      => true,
                'type'                 => 'multichoice',
            ]
        );
    }
}
