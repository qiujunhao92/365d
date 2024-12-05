
const oceanapplepay_settings = window.wc.wcSettings.getSetting( 'oceanapplepay_data', {} );


const oceanapplepay_label = window.wp.htmlEntities.decodeEntities( oceanapplepay_settings.title ) || window.wp.i18n.__( 'Oceanpayment ApplePay Payment Gateway', 'oceanpayment-applepay-gateway' );




const oceanapplepay_Content = () => {
    return window.wp.htmlEntities.decodeEntities( oceanapplepay_settings.description || '' );
};


var I = function(e) {
    var t = e.components,
        n = e.title,
        r = e.icons,
        a = e.id;
    Array.isArray(r) || (r = [r]);
    var o = t.PaymentMethodLabel,
        i = t.PaymentMethodIcons;

    const style = {
        'align-items': 'center',
        'display': 'flex',
        'width': '100%'
    };

    return React.createElement("div", {
        className: "wc-oceanapplepay-blocks-payment-method__label ".concat(a),
        style:style
    }, React.createElement(o, {
        text: n
    }), React.createElement(i, {
        icons: r
    }))
};
const Oceanapplepay_Block_Gateway = {
    name: 'oceanapplepay',

    label: React.createElement(I, {
        id: "oceanapplepay",
        title: oceanapplepay_settings.title,
        icons: oceanapplepay_settings.icons
    }),

    content: Object( window.wp.element.createElement )( oceanapplepay_Content, null ),
    edit: Object( window.wp.element.createElement )( oceanapplepay_Content, null ),
    canMakePayment: () => true,
    ariaLabel: oceanapplepay_label,
    // placeOrderButtonLabel: window.wp.i18n.__( 'Proceed to Oceanpayment', 'oceanpayment-applepay-gateway' ),
  /*  supports: {
        features: oceanapplepay_settings.supports,
    },*/
};

window.wc.wcBlocksRegistry.registerPaymentMethod( Oceanapplepay_Block_Gateway );