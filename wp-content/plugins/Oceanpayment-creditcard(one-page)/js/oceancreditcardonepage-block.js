
const oceancreditcardonepage_settings = window.wc.wcSettings.getSetting( 'oceancreditcardonepage_data', {} );


const oceancreditcardonepage_label = window.wp.htmlEntities.decodeEntities( oceancreditcardonepage_settings.title ) || window.wp.i18n.__( 'Oceanpayment CreditCard Onepage Payment Gateway', 'oceanpayment-creditcardonepage-gateway' );




const oceancreditcardonepage_Content = (props ) => {
    var submiturl = '';
    if(oceancreditcardonepage_settings.submiturl != 'https://secure.oceanpayment.com/gateway/direct/pay'){
        submiturl = oceancreditcardonepage_settings.submiturl;
    }

    const script_html= 'jQuery(function() {\n' +
        '                    //如需修改支付语言，可传入语言代码\n' +
        '                    onePageCardData.init("'+submiturl+'","'+oceancreditcardonepage_settings.cssurl+'","'+oceancreditcardonepage_settings.language+'","'+oceancreditcardonepage_settings.public_key+'","'+oceancreditcardonepage_settings.SSL+oceancreditcardonepage_settings.HTTP_HOST+'");\n' +
        '\n' +
        '                    $("#op-payment-icons img").css("display", "inline-block");\n' +
        '                });\n' +
        '                \n' +
        '                var oceanpaymentCallBack = function(data){\n' +
        '                    $("#errorMsg").val(data.errorMsg);\n' +
        '                    $("#card_data").text(data.card_data);\n' +
        '\n' +
        '                }\n' +
        '              \n' +
        '                var oceanPlaceOrder = document.getElementsByClassName("wc-block-components-checkout-place-order-button");\n' +
        '                \n' +
        '                if(oceanPlaceOrder){    \n' +
        '         \t               \n' +
        '                    oceanPlaceOrder.onclick = function(){\n' +
        '\n' +
        '     \t        \t    var oceanCheckOnepage = document.getElementById("radio-control-wc-payment-method-options-oceancreditcardonepage").checked;\n' +
        '    \n' +
        '      \t        \t    if(oceanCheckOnepage == true){\n' +
        '            \t     \n' +
        '                        \tonePageCardData.OceanpaymentValidateResult();\n' +
        '                            \n' +
        '                        \tif($("#card_data").text() == \'\'){                            \t\n' +
        '                        \t\treturn false;\n' +
        '                            }else{\n' +
        '                                if($("#errorMsg").val() != \'\'){\n' +
        '                                \treturn false;\n' +
        '                                }\n' +
        '                            }                       \t \n' +
        '                        }                 \n' +
        '                    }\n' +
        '                }';





    var text =  React.createElement("div", {id:'card_data_from'},React.createElement("div", {className:'op-payment-icons',id:'oceanpayment-element'})
        ,React.createElement("input", {name:'errorMsg',id:'errorMsg',value:'',type:'hidden'})
    );


    const { eventRegistration, emitResponse } = props;
    const { onPaymentSetup } = eventRegistration;


    window.wp.element.useEffect(() => {

        const div = document.createElement('div');
        div.className='card_data';
        div.id='card_data';
        div.style.display='none';
        div.innerHTML = '';
        document.getElementById('card_data_from').appendChild(div);
        //添加js
        const script = document.createElement('script');
        script.innerHTML = script_html;

        document.getElementById('card_data_from').appendChild(script);
    }, []);


    var data_res = '';
    $(document).ready(function(){

        $('.wc-block-components-checkout-place-order-button').click(function () {

            data_res = $('#card_data').text();
            console.log(data_res);
        })
    })

    window.wp.element.useEffect( () => {

        const unsubscribe = onPaymentSetup( async () => {

            const myGatewayCardData = data_res;
            const customDataIsValid = !! myGatewayCardData.length;
            if ( customDataIsValid ) {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            myGatewayCardData,
                        },
                    },
                };
            }

            return {
                type: emitResponse.responseTypes.ERROR,
                message: 'There was an error',
            };
        } );
        // Unsubscribes when this component is unmounted.
        return () => {
            unsubscribe();
        };
    }, [
        emitResponse.responseTypes.ERROR,
        emitResponse.responseTypes.SUCCESS,
        onPaymentSetup,
    ] );
    return text;

};


var I = function(e) {
    var t = e.components,
        n = e.title,
        r = e.icons,
        a = e.id;
    Array.isArray(r) || (r = [r]);
    var o = t.PaymentMethodLabel,
        i = t.PaymentMethodIcons;
    return React.createElement("div", {
        className: "wc-oceancreditcardonepage-blocks-payment-method__label ".concat(a)
    }, React.createElement(o, {
        text: n
    }), React.createElement(i, {
        icons: r
    }))
};


const Oceancreditcardonepage_Block_Gateway = {
    name: 'oceancreditcardonepage',

    label: React.createElement(I, {
        id: "oceancreditcardonepage",
        title: oceancreditcardonepage_settings.title,
        icons: oceancreditcardonepage_settings.icons
    }),

    content: Object( window.wp.element.createElement )( oceancreditcardonepage_Content, null ),
    edit: Object( window.wp.element.createElement )( oceancreditcardonepage_Content, null ),
    canMakePayment: () => true,
    ariaLabel: oceancreditcardonepage_label,
    placeOrderButtonLabel: window.wp.i18n.__( 'Proceed to pay', 'oceanpayment-creditcard-gateway' ),

};

window.wc.wcBlocksRegistry.registerPaymentMethod( Oceancreditcardonepage_Block_Gateway );