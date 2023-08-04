const { registerBlockType } = wp.blocks;
const { dispatch, select } = wp.data;
const { ToggleControl, PanelBody } = wp.components;
const { InspectorControls } = wp.blockEditor;
import classnames from 'classnames';
const __ = Drupal.t;
import { v4 as uuidv4 } from 'uuid';

const accordion_settings = {
  title: __('Accordion'),
  icon: 'editor-justify',
  providesContext: {
    'sl-bootstrap/accordion-id': 'id',
    'sl-bootstrap/accordion-always-open': 'alwaysOpen',
  },
  attributes: {
    'id': {
      'type': 'string',
    },
    'openFirstItem': {
      type: 'boolean',
    },
    'flush': {
      type: 'boolean',
    },
    'alwaysOpen': {
      type: 'boolean',
    },
  },

  edit({ className, attributes, setAttributes }) {
    const { id, openFirstItem, flush, alwaysOpen } = attributes;

    if (id === undefined) {
      setAttributes({
        id: 'accordion-' + uuidv4(),
        openFirstItem: false,
        flush: false,
        alwaysOpen: false,
      });
    }

    return (
      <>
        <div id={id}
             className={classnames(className, 'accordion', { 'accordion-flush': flush })}>
          <InnerBlocks allowedBlocks={['bootstrap/accordion-item']}/>
        </div>

        <InspectorControls>
          <PanelBody title={__('Settings')}>
            <ToggleControl
              label={__('Open first item')}
              help={__('Opens the first item when the page is loaded.')}
              checked={openFirstItem}
              onChange={() => setAttributes({ openFirstItem: !openFirstItem })}
            />
            <ToggleControl
              label={__('Flush')}
              help={__('Removes the default background-color, some borders, and some rounded corners to render accordions edge-to-edge with their parent container.')}
              checked={flush}
              onChange={() => setAttributes({ flush: !flush })}
            />
            <ToggleControl
              label={__('Always Open')}
              help={__('Makes accordion items stay open when another item is opened.')}
              checked={alwaysOpen}
              onChange={() => setAttributes({ alwaysOpen: !alwaysOpen })}
            />
          </PanelBody>
        </InspectorControls>
      </>
    );
  },

  save() {
    return (
      <InnerBlocks.Content/>
    );
  },
};

const category = {
  slug: 'sl-bootstrap',
  title: __('Bootstrap'),
};

const currentCategories = select('core/blocks').getCategories().filter(item => item.slug !== category.slug);
dispatch('core/blocks').setCategories([category, ...currentCategories]);
registerBlockType(`${category.slug}/accordion`, { category: category.slug, ...accordion_settings });