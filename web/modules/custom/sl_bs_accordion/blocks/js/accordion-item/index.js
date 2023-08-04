const { registerBlockType } = wp.blocks;
const { useEffect } = wp.element;
const { dispatch, select } = wp.data;
const { RichText } = wp.blockEditor;
import classnames from 'classnames';
const __ = Drupal.t;
import { v4 as uuidv4 } from 'uuid';

const accordion_item_settings = {
  title: __('Accordion Item'),
  icon: 'align-wide',
  usesContext: [
    'sl-bootstrap/accordion-id',
    'sl-bootstrap/accordion-always-open',
  ],
  attributes: {
    accordionId: {
      type: 'string',
    },
    accordionAlwaysOpen: {
      type: 'boolean',
    },
    idHeading: {
      type: 'string',
    },
    idCollapse: {
      type: 'string',
    },
    heading: {
      type: 'string',
    },
  },

  edit({ className, attributes, setAttributes, context }) {
    const {
      accordionId,
      accordionAlwaysOpen,
      idHeading,
      idCollapse,
      heading,
    } = attributes;

    if (idHeading === undefined && idCollapse === undefined) {
      setAttributes({
        idHeading: 'heading-' + uuidv4(),
        idCollapse: 'collapse-' + uuidv4(),
        accordionAlwaysOpen: false,
        heading: 'Accordion Item',
      });
    }

    useEffect(() => {
      setAttributes({ accordionId: context['sl-bootstrap/accordion-id'] });
    }, [context['sl-bootstrap/accordion-id']]);

    useEffect(() => {
      setAttributes({ accordionAlwaysOpen: context['sl-bootstrap/accordion-always-open'] });
    }, [context['sl-bootstrap/accordion-always-open']]);

    return (
      <div className={classnames(className, 'accordion-item')}>
        <h2 className="accordion-header" id={idHeading}>
          <button className="accordion-button" type="button">
            <RichText
              identifier="heading"
              tagName="span"
              value={heading}
              placeholder={__('Accordion Item')}
              onChange={heading => {
                setAttributes({
                  heading: heading,
                });
              }}
            />
            <span data-bs-target={`#${idCollapse}`}
                  className="expand-collapse collapsed" aria-expanded="true"
                  data-bs-toggle="collapse"
                  aria-controls={idCollapse}>{__('<Click here to expand or collapse>')}</span>
          </button>
        </h2>

        <div id={idCollapse} className="accordion-collapse collapse"
             aria-labelledby={idHeading}
             data-bs-parent={accordionAlwaysOpen ? null : `#${accordionId}`}>
          <div className="accordion-body">
            <InnerBlocks/>
          </div>
        </div>

      </div>
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
registerBlockType(`${category.slug}/accordion-item`, {
  category: category.slug,
  parent: ['sl-bootstrap/accordion'], ...accordion_item_settings,
});