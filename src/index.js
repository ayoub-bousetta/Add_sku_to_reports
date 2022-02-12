// Import SCSS entry file so that webpack picks up changes
import './index.scss';
import {addFilter} from '@wordpress/hooks';
import {__} from '@wordpress/i18n';

const addTableColumn = reportTableData => {
    if ('orders' !== reportTableData.endpoint) {
        return reportTableData;
    }

    const newHeaders = [
        ...reportTableData.headers,
        {
            label: __( 'SKU', 'woocommerce' ),
            key: 'products_sku',
            required: false,
            
        },
    ];
  console.log(newHeaders);
    const newRows = reportTableData.rows.map((row, index) => {
        const item = reportTableData.items.data[index];

         
        const newRow = [
            ...row,
            {
                display: item.products_sku,
                value: item.products_sku,
            },
        ];
        return newRow;
    });

    reportTableData.headers = newHeaders;
    reportTableData.rows = newRows;

    return reportTableData;
};

addFilter('woocommerce_admin_report_table', 'wg-woocommerce-addon', addTableColumn);