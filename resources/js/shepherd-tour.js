import Shepherd from 'shepherd.js';
import 'shepherd.js/dist/css/shepherd.css';

// Static Tour Steps - قيم ثابتة للجولة
const tourStepsData = [
    // Welcome Step
    {
        id: 'welcome',
        title: '👋 مرحباً بك في جولة النظام!',
        text: '<strong>سنقوم بجولة شاملة لشرح جميع المميزات والأقسام في النظام المالي الخاص بك.</strong><br><br>الجولة تستغرق بضع دقائق فقط وستساعدك على فهم كل ميزة في النظام.',
        attachTo: null,
        position: 'center',
        buttons: [
            { text: 'تخطي الجولة', action: 'cancel', secondary: true },
            { text: 'ابدأ الجولة', action: 'next', secondary: false }
        ]
    },
    
    // Merchants Section
    {
        id: 'merchants-list',
        title: 'التجار',
        text: '<br>هنا تجد قائمة كاملة بجميع التجار.<br><br>يمكنك:<br>• إضافة تاجر جديد<br>• عرض تفاصيل كل تاجر<br>• تعديل معلومات التاجر<br>• متابعة الحسابات المالية معه',
        attachTo: '[data-tour="merchants-list"]',
        position: 'top'
    },
    
    {
        id: 'merchant-wallets',
        title: 'محافظ التجار',
        text: '<br>إدارة محافظ التجار والحسابات المالية.<br><br>من هنا يمكنك:<br>• متابعة رصيد كل تاجر<br>• إضافة محفظة جديدة<br>• تتبع المعاملات المالية<br>• إدارة حسابات متعددة لكل تاجر',
        attachTo: '[data-tour="merchant-wallets"]',
        position: 'right'
    },
    
    {
        id: 'merchant-products',
        title: 'منتجات التجار',
        text: '<br>إدارة منتجات كل تاجر وأسعارها.<br><br>الميزات:<br>• إضافة منتجات جديدة<br>• تحديد الأسعار والوحدات<br>• متابعة المخزون<br>• ربط المنتجات بالطلبات',
        attachTo: '[data-tour="merchant-products"]',
        position: 'right'
    },
    
    {
        id: 'merchant-statements',
        title: 'كشوفات حسابات التجار',
        text: '<br>كشوف حسابات تفصيلية لكل تاجر.<br><br>يتيح لك:<br>• مراجعة الحركات المالية<br>• تتبع الديون والمستحقات<br>• طباعة كشوفات الحساب<br>• تحليل التعاملات المالية',
        attachTo: '[data-tour="merchant-statements"]',
        position: 'right'
    },
    
    {
        id: 'merchant-orders',
        title: 'الطلبات',
        text: '<br>إدارة جميع طلبات الشراء من التجار.<br><br>من خلال هذا القسم:<br>• إنشاء طلب جديد<br>• متابعة حالة الطلبات<br>• إضافة منتجات للطلب<br>• حساب التكاليف الإجمالية',
        attachTo: '[data-tour="merchant-orders"]',
        position: 'right'
    },
    
    // Financial Section
    {
        id: 'financial-group',
        title: 'القيود والمالية',
        text: '<strong>القسم المالي والمحاسبي الشامل.</strong><br><br>هذا القسم يحتوي على جميع القيود المحاسبية والحركات المالية لإدارة حساباتك بشكل احترافي.<br><br>يشمل القيود اليومية والمعاملات المالية.',
        attachTo: '[data-tour="financial-group"]',
        position: 'right'
    },
    
    {
        id: 'account-entries',
        title: 'القيود المحاسبية',
        text: '<br>إدارة القيود المحاسبية (Account Entries).<br><br>الوظائف الرئيسية:<br>• إنشاء قيود يومية<br>• تسجيل المدفوعات والمقبوضات<br>• متابعة الأرصدة<br>• إصدار تقارير مالية<br>• توثيق جميع الحركات المالية',
        attachTo: '[data-tour="account-entries"]',
        position: 'right'
    },
    
    {
        id: 'payment-transactions',
        title: 'الحركات المالية',
        text: '<br>تتبع جميع المعاملات المالية (Payment Transactions).<br><br>يشمل:<br>• معاملات الدفع والاستلام<br>• تحويلات بين الحسابات<br>• سجل كامل للمعاملات<br>• حالة كل معاملة (معلقة، مكتملة، ملغاة)<br>• ربط المعاملات بالقيود المحاسبية',
        attachTo: '[data-tour="payment-transactions"]',
        position: 'right'
    },
    
    // Budget Section
    {
        id: 'budget-group',
        title: ' الميزانية الشخصية',
        text: '<strong>إدارة ميزانيتك الشخصية بذكاء.</strong><br><br>هذا القسم يساعدك على:<br>• التخطيط المالي<br>• متابعة الإنفاق<br>• تحقيق الأهداف المالية<br>• تحليل عادات الإنفاق<br>• التحكم في المصروفات',
        attachTo: '[data-tour="budget-group"]',
        position: 'right'
    },
    
    {
        id: 'budgets',
        title: 'الميزانيات',
        text: '<br>إنشاء وإدارة الميزانيات المختلفة.<br><br>يمكنك:<br>• إنشاء ميزانية شهرية أو سنوية<br>• تحديد حدود الإنفاق<br>• متابعة التقدم<br>• إشعارات عند تجاوز الحد<br>• مقارنة الإنفاق الفعلي بالمخطط',
        attachTo: '[data-tour="budgets"]',
        position: 'right'
    },
    
    {
        id: 'budget-categories',
        title: 'فئات الميزانية',
        text: '<br>تنظيم الميزانية حسب الفئات.<br><br>أمثلة على الفئات:<br>• الطعام والشراب<br>• النقل والمواصلات<br>• الفواتير والخدمات<br>• الترفيه<br>• المدخرات<br><br>كل فئة لها ميزانية وتقارير منفصلة.',
        attachTo: '[data-tour="budget-categories"]',
        position: 'right'
    },
    
    // Settings Section
    {
        id: 'settings-group',
        title: 'الإعدادات',
        text: '<strong>تخصيص النظام حسب احتياجاتك.</strong><br><br>قسم الإعدادات يتيح لك التحكم الكامل في تفضيلاتك وبياناتك.<br><br>يشمل الإعدادات المالية وإدارة البيانات الشخصية.',
        attachTo: '[data-tour="settings-group"]',
        position: 'right'
    },
    
    {
        id: 'financial-settings',
        title: 'الإعدادات المالية',
        text: '<br>ضبط الإعدادات المالية الأساسية.<br><br>يشمل:<br>• تحديد الراتب الشهري<br>• حدود المشتريات (الأدنى والأقصى)<br>• الحد الأقصى للديون<br>• نسب التحذير من الديون<br>• إشعارات المخاطر المالية',
        attachTo: '[data-tour="financial-settings"]',
        position: 'right'
    },
    
    {
        id: 'personal-data',
        title: 'إدارة البيانات الشخصية',
        text: '<br>تحكم كامل في بياناتك الشخصية.<br><br>الخيارات المتاحة:<br>• تصدير جميع بياناتك (JSON)<br>• تصدير إلى Excel<br>• استيراد البيانات<br>• حذف الحساب نهائياً<br>• حماية الخصوصية',
        attachTo: '[data-tour="personal-data"]',
        position: 'right'
    },
    
    // Final Step
    {
        id: 'finish',
        title: 'تهانينا! انتهت الجولة',
        text: '<strong>الآن أصبحت جاهزاً لاستخدام النظام!</strong><br><br>لقد تعرفت على:<br>• إدارة التجار والمنتجات والطلبات<br>• القيود والحركات المالية<br>• الميزانية الشخصية وفئاتها<br>• الإعدادات المختلفة<br>• إدارة البيانات الشخصية<br><br>يمكنك إعادة الجولة في أي وقت من خلال النقر على أيقونة 🎓 في الأعلى.',
        attachTo: null,
        position: 'center',
        buttons: [
            { text: 'السابق', action: 'back', secondary: true },
            { text: 'إنهاء الجولة', action: 'complete', secondary: false }
        ]
    }
];

// Initialize tour
export function initializeShepherdTour() {
    // Create a new tour instance
    const tour = new Shepherd.Tour({
        useModalOverlay: true,
        defaultStepOptions: {
            classes: 'shepherd-theme-custom',
            scrollTo: { behavior: 'smooth', block: 'center' },
            cancelIcon: {
                enabled: true,
                label: 'إغلاق'
            },
            modalOverlayOpeningRadius: 12,
            modalOverlayOpeningPadding: 10
        },
        tourName: 'app-tour'
    });

    // Add steps from static data
    tourStepsData.forEach((stepData, index) => {
        const stepConfig = {
            id: stepData.id,
            title: stepData.title,
            text: stepData.text,
        };

        // Auto-detect and attach to elements
        if (stepData.attachTo) {
            const element = document.querySelector(stepData.attachTo);
            if (element) {
                stepConfig.attachTo = {
                    element: element,
                    on: stepData.position || 'right'
                };
                console.log(`Found element for step ${stepData.id}`);
            } else {
                console.log(`Element not found for step ${stepData.id}, showing in center`);
                // Show in center if element not found
            }
        }

        // Build buttons
        const buttons = [];
        const stepButtons = stepData.buttons || [
            { text: 'السابق', action: 'back', secondary: true },
            { text: 'التالي', action: 'next', secondary: false }
        ];

        stepButtons.forEach(btnData => {
            const button = {
                text: btnData.text,
                secondary: btnData.secondary || false
            };

            // Handle button actions
            if (btnData.action === 'back') {
                button.action = tour.back;
            } else if (btnData.action === 'next') {
                button.action = tour.next;
            } else if (btnData.action === 'cancel') {
                button.action = tour.cancel;
            } else if (btnData.action === 'complete') {
                button.action = function() {
                    tour.complete();
                    // Save completion
                    localStorage.setItem('shepherd-tour-completed', 'true');
                    localStorage.setItem('shepherd-tour-completed-at', new Date().toISOString());
                };
            }

            buttons.push(button);
        });

        stepConfig.buttons = buttons;

        // Add the step to the tour
        tour.addStep(stepConfig);
    });

    return tour;
}

// Auto-detect navigation elements and add data-tour attributes
function autoDetectNavigationElements() {
    const navigationMap = {
        // Dashboard
        'dashboard': 'لوحة التحكم',
        
        // Merchants
        'merchants-list': 'التجار',
        'merchant-wallets': 'محافظ التجار',
        'merchant-products': 'منتجات التجار',
        'merchant-statements': 'كشوفات حسابات التجار',
        'merchant-orders': 'الطلبات',
        
        // Financial
        'financial-group': 'القيود & المالية',
        'account-entries': 'القيود',
        'payment-transactions': 'الحركات المالية',
        
        // Budget
        'budget-group': 'الميزانية الشخصية',
        'budgets': 'الميزانيات',
        'budget-categories': 'فئات الميزانية',
        
        // Settings
        'settings-group': 'الإعدادات',
        'financial-settings': 'الإعدادات المالية',
        'personal-data': 'إدارة البيانات الشخصية'
    };

    // Find all navigation items
    const navItems = document.querySelectorAll('.fi-sidebar-item, .fi-sidebar-group, [role="menuitem"], a[href*="/admin"]');
    
    navItems.forEach(item => {
        const text = item.textContent.trim();
        const link = item.querySelector('a') || item;
        
        // Check each mapping
        Object.entries(navigationMap).forEach(([tourId, navText]) => {
            if (text.includes(navText) || text === navText) {
                link.setAttribute('data-tour', tourId);
                console.log(`Auto-detected: ${navText} → ${tourId}`);
            }
        });
    });
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-detect navigation elements
    autoDetectNavigationElements();
    
    // Re-run detection when navigation updates
    setTimeout(autoDetectNavigationElements, 1000);
    
    // Watch for navigation changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                setTimeout(autoDetectNavigationElements, 100);
            }
        });
    });
    
    const sidebar = document.querySelector('.fi-sidebar');
    if (sidebar) {
        observer.observe(sidebar, {
            childList: true,
            subtree: true
        });
    }

    // Check if user wants to see the tour
    const tourButtons = document.querySelectorAll('[data-shepherd-tour-trigger]');
    tourButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            try {
                const tour = initializeShepherdTour();
                tour.start();
            } catch (error) {
                console.error('Error starting tour:', error);
                alert('حدث خطأ أثناء بدء الجولة. يرجى المحاولة مرة أخرى.');
            }
        });
    });
});

// ==================== Resource Tours Configuration ====================
// تكوين الجولات لجميع الـ Resources
const resourceToursConfig = {
    'user-merchants': {
        tourName: 'user-merchant-resource-tour',
        storageKey: 'user-merchant-tour-completed',
        dataTour: 'merchants-list',
        urlPattern: '/user-merchants',
        menuText: 'التجار',
        steps: {
            welcome: {
                title: '👋 مرحباً بإضافة تاجر جديد!',
                text: '<strong>دعنا نريك أين تجد هذا القسم في القائمة الجانبية.</strong><br><br>سنقوم بعرض موقع قسم "التجار" في قائمة التنقل لتتمكن من الوصول إليه بسهولة في المستقبل.'
            },
            menu: {
                title: '🏪 قسم التجار',
                text: '<strong>هذا هو قسم "التجار" في القائمة!</strong><br><br>🔍 <strong>Resource:</strong> UserMerchantResource<br>📂 <strong>الموقع:</strong> app/Filament/Resources/UserMerchants/UserMerchantResource.php<br><br>من هنا يمكنك:<br>• عرض قائمة جميع التجار<br>• إضافة تاجر جديد<br>• تعديل بيانات التجار<br>• إدارة الحسابات المالية مع كل تاجر'
            },
            form: {
                title: '📝 نموذج إضافة التاجر',
                text: '<strong>أنت الآن في صفحة إضافة تاجر جديد.</strong><br><br>من هذا النموذج يمكنك:<br>• إدخال اسم التاجر<br>• إضافة بريد إلكتروني ورقم هاتف<br>• تحديد فئة الميزانية<br>• تحديد تصنيف التاجر<br>• إضافة معلومات إضافية<br><br>بعد حفظ التاجر، سيظهر في قائمة التجار التي رأيناها للتو!'
            }
        }
    },
    'user-merchant-wallets': {
        tourName: 'wallet-resource-tour',
        storageKey: 'wallet-tour-completed',
        dataTour: 'merchant-wallets',
        urlPattern: '/user-merchant-wallets',
        menuText: 'محافظ التجار',
        steps: {
            welcome: {
                title: '💼 مرحباً بإضافة محفظة تاجر!',
                text: '<strong>المحافظ تساعدك على إدارة الحسابات المالية مع كل تاجر.</strong><br><br>دعنا نريك موقع هذا القسم في القائمة.'
            },
            menu: {
                title: '💼 محافظ التجار',
                text: '<strong>هذا هو قسم "محافظ التجار"!</strong><br><br>🔍 <strong>Resource:</strong> UserMerchantWalletResource<br><br>من هنا يمكنك:<br>• متابعة رصيد كل تاجر<br>• إضافة محفظة جديدة<br>• تتبع المعاملات المالية<br>• إدارة حسابات متعددة لكل تاجر'
            },
            form: {
                title: '📝 نموذج المحفظة',
                text: '<strong>املأ بيانات المحفظة.</strong><br><br>يمكنك:<br>• اختيار التاجر<br>• تحديد نوع المحفظة<br>• إضافة رصيد افتتاحي<br>• إضافة ملاحظات'
            }
        }
    },
    'user-merchant-products': {
        tourName: 'product-resource-tour',
        storageKey: 'product-tour-completed',
        dataTour: 'merchant-products',
        urlPattern: '/user-merchant-products',
        menuText: 'منتجات التجار',
        steps: {
            welcome: {
                title: '📦 مرحباً بإضافة منتج!',
                text: '<strong>إدارة منتجات التجار وأسعارها.</strong><br><br>دعنا نريك موقع هذا القسم في القائمة.'
            },
            menu: {
                title: '📦 منتجات التجار',
                text: '<strong>هذا هو قسم "منتجات التجار"!</strong><br><br>🔍 <strong>Resource:</strong> UserMerchantProductResource<br><br>الميزات:<br>• إضافة منتجات جديدة<br>• تحديد الأسعار والوحدات<br>• متابعة المخزون<br>• ربط المنتجات بالطلبات'
            },
            form: {
                title: '📝 نموذج المنتج',
                text: '<strong>أضف منتج جديد.</strong><br><br>يمكنك:<br>• اختيار التاجر<br>• إدخال اسم المنتج<br>• تحديد السعر<br>• تحديد وحدة القياس'
            }
        }
    },
    'user-merchant-account-statements': {
        tourName: 'statement-resource-tour',
        storageKey: 'statement-tour-completed',
        dataTour: 'merchant-statements',
        urlPattern: '/user-merchant-account-statements',
        menuText: 'كشوفات حسابات التجار',
        steps: {
            welcome: {
                title: '📊 مرحباً بكشف الحساب!',
                text: '<strong>كشوفات حسابات تفصيلية لكل تاجر.</strong><br><br>دعنا نريك موقع هذا القسم في القائمة.'
            },
            menu: {
                title: '📊 كشوفات حسابات التجار',
                text: '<strong>هذا هو قسم "كشوفات حسابات التجار"!</strong><br><br>🔍 <strong>Resource:</strong> UserMerchantAccountStatementResource<br><br>يتيح لك:<br>• مراجعة الحركات المالية<br>• تتبع الديون والمستحقات<br>• طباعة كشوفات الحساب<br>• تحليل التعاملات المالية'
            },
            form: {
                title: '📝 نموذج كشف الحساب',
                text: '<strong>إنشاء كشف حساب جديد.</strong><br><br>يمكنك:<br>• اختيار التاجر<br>• تحديد الفترة الزمنية<br>• عرض الحركات المالية<br>• حساب الأرصدة'
            }
        }
    },
    'user-merchant-orders': {
        tourName: 'order-resource-tour',
        storageKey: 'order-tour-completed',
        dataTour: 'merchant-orders',
        urlPattern: '/user-merchant-orders',
        menuText: 'الطلبات',
        steps: {
            welcome: {
                title: '🛒 مرحباً بإضافة طلب!',
                text: '<strong>إدارة جميع طلبات الشراء من التجار.</strong><br><br>دعنا نريك موقع هذا القسم في القائمة.'
            },
            menu: {
                title: '🛒 الطلبات',
                text: '<strong>هذا هو قسم "الطلبات"!</strong><br><br>🔍 <strong>Resource:</strong> UserMerchantOrderResource<br><br>من خلال هذا القسم:<br>• إنشاء طلب جديد<br>• متابعة حالة الطلبات<br>• إضافة منتجات للطلب<br>• حساب التكاليف الإجمالية'
            },
            form: {
                title: '📝 نموذج الطلب',
                text: '<strong>إنشاء طلب شراء جديد.</strong><br><br>يمكنك:<br>• اختيار التاجر<br>• إضافة المنتجات<br>• تحديد الكميات<br>• حساب الإجمالي<br>• تحديد طريقة الدفع'
            }
        }
    },
    'user-merchant-account-entries': {
        tourName: 'entry-resource-tour',
        storageKey: 'entry-tour-completed',
        dataTour: 'account-entries',
        urlPattern: '/user-merchant-account-entries',
        menuText: 'القيود',
        steps: {
            welcome: {
                title: '📒 مرحباً بإضافة قيد محاسبي!',
                text: '<strong>إدارة القيود المحاسبية (Account Entries).</strong><br><br>دعنا نريك موقع هذا القسم في القائمة.'
            },
            menu: {
                title: '📒 القيود المحاسبية',
                text: '<strong>هذا هو قسم "القيود"!</strong><br><br>🔍 <strong>Resource:</strong> UserMerchantAccountEntryResource<br><br>الوظائف الرئيسية:<br>• إنشاء قيود يومية<br>• تسجيل المدفوعات والمقبوضات<br>• متابعة الأرصدة<br>• إصدار تقارير مالية<br>• توثيق جميع الحركات المالية'
            },
            form: {
                title: '📝 نموذج القيد',
                text: '<strong>إنشاء قيد محاسبي جديد.</strong><br><br>يمكنك:<br>• اختيار التاجر<br>• تحديد نوع القيد (مدين/دائن)<br>• إدخال المبلغ<br>• إضافة وصف<br>• تحديد التاريخ'
            }
        }
    },
    'user-merchant-payment-transactions': {
        tourName: 'transaction-resource-tour',
        storageKey: 'transaction-tour-completed',
        dataTour: 'payment-transactions',
        urlPattern: '/user-merchant-payment-transactions',
        menuText: 'الحركات المالية',
        steps: {
            welcome: {
                title: '💳 مرحباً بإضافة حركة مالية!',
                text: '<strong>تتبع جميع المعاملات المالية (Payment Transactions).</strong><br><br>دعنا نريك موقع هذا القسم في القائمة.'
            },
            menu: {
                title: '💳 الحركات المالية',
                text: '<strong>هذا هو قسم "الحركات المالية"!</strong><br><br>🔍 <strong>Resource:</strong> UserMerchantPaymentTransactionResource<br><br>يشمل:<br>• معاملات الدفع والاستلام<br>• تحويلات بين الحسابات<br>• سجل كامل للمعاملات<br>• حالة كل معاملة<br>• ربط المعاملات بالقيود المحاسبية'
            },
            form: {
                title: '📝 نموذج الحركة المالية',
                text: '<strong>تسجيل معاملة مالية جديدة.</strong><br><br>يمكنك:<br>• اختيار التاجر<br>• تحديد نوع المعاملة<br>• إدخال المبلغ<br>• اختيار طريقة الدفع<br>• تحديد الحالة'
            }
        }
    },
    'budgets': {
        tourName: 'budget-resource-tour',
        storageKey: 'budget-tour-completed',
        dataTour: 'budgets',
        urlPattern: '/budgets',
        menuText: 'الميزانيات',
        steps: {
            welcome: {
                title: '💰 مرحباً بإنشاء ميزانية!',
                text: '<strong>إنشاء وإدارة الميزانيات المختلفة.</strong><br><br>دعنا نريك موقع هذا القسم في القائمة.'
            },
            menu: {
                title: '💰 الميزانيات',
                text: '<strong>هذا هو قسم "الميزانيات"!</strong><br><br>🔍 <strong>Resource:</strong> BudgetResource<br><br>يمكنك:<br>• إنشاء ميزانية شهرية أو سنوية<br>• تحديد حدود الإنفاق<br>• متابعة التقدم<br>• إشعارات عند تجاوز الحد<br>• مقارنة الإنفاق الفعلي بالمخطط'
            },
            form: {
                title: '📝 نموذج الميزانية',
                text: '<strong>إنشاء ميزانية جديدة.</strong><br><br>يمكنك:<br>• تسمية الميزانية<br>• تحديد المبلغ المخطط<br>• اختيار الفترة (شهرية/سنوية)<br>• ربطها بفئات الإنفاق<br>• تفعيل التنبيهات'
            }
        }
    },
    'budget-categories': {
        tourName: 'budget-category-resource-tour',
        storageKey: 'budget-category-tour-completed',
        dataTour: 'budget-categories',
        urlPattern: '/budget-categories',
        menuText: 'فئات الميزانية',
        steps: {
            welcome: {
                title: '📁 مرحباً بإنشاء فئة ميزانية!',
                text: '<strong>تنظيم الميزانية حسب الفئات.</strong><br><br>دعنا نريك موقع هذا القسم في القائمة.'
            },
            menu: {
                title: '📁 فئات الميزانية',
                text: '<strong>هذا هو قسم "فئات الميزانية"!</strong><br><br>🔍 <strong>Resource:</strong> BudgetCategoryResource<br><br>أمثلة على الفئات:<br>• الطعام والشراب<br>• النقل والمواصلات<br>• الفواتير والخدمات<br>• الترفيه<br>• المدخرات<br><br>كل فئة لها ميزانية وتقارير منفصلة.'
            },
            form: {
                title: '📝 نموذج فئة الميزانية',
                text: '<strong>إنشاء فئة ميزانية جديدة.</strong><br><br>يمكنك:<br>• تسمية الفئة<br>• تحديد اللون المميز<br>• تحديد حد الإنفاق<br>• إضافة وصف<br>• ربطها بميزانية رئيسية'
            }
        }
    },
    'financial-settings': {
        tourName: 'financial-settings-tour',
        storageKey: 'financial-settings-tour-completed',
        dataTour: 'financial-settings',
        urlPattern: '/financial-settings',
        menuText: 'الإعدادات المالية',
        steps: {
            welcome: {
                title: '⚙️ مرحباً بالإعدادات المالية!',
                text: '<strong>ضبط الإعدادات المالية الأساسية.</strong><br><br>دعنا نريك موقع هذا القسم في القائمة.'
            },
            menu: {
                title: '⚙️ الإعدادات المالية',
                text: '<strong>هذا هو قسم "الإعدادات المالية"!</strong><br><br>🔍 <strong>Page:</strong> ManageFinancialSettings<br><br>يشمل:<br>• تحديد الراتب الشهري<br>• حدود المشتريات<br>• الحد الأقصى للديون<br>• نسب التحذير<br>• إشعارات المخاطر المالية'
            },
            form: {
                title: '📝 نموذج الإعدادات',
                text: '<strong>اضبط إعداداتك المالية.</strong><br><br>يمكنك:<br>• تحديد الدخل الشهري<br>• ضبط حدود الإنفاق<br>• تفعيل التنبيهات<br>• إدارة المخاطر المالية<br>• حفظ التفضيلات'
            }
        }
    },
    'personal-data': {
        tourName: 'personal-data-tour',
        storageKey: 'personal-data-tour-completed',
        dataTour: 'personal-data',
        urlPattern: '/personal-data',
        menuText: 'إدارة البيانات الشخصية',
        steps: {
            welcome: {
                title: '🔒 مرحباً بإدارة البيانات!',
                text: '<strong>تحكم كامل في بياناتك الشخصية.</strong><br><br>دعنا نريك موقع هذا القسم في القائمة.'
            },
            menu: {
                title: '🔒 إدارة البيانات الشخصية',
                text: '<strong>هذا هو قسم "إدارة البيانات الشخصية"!</strong><br><br>🔍 <strong>Page:</strong> ManagePersonalData<br><br>الخيارات المتاحة:<br>• تصدير جميع بياناتك (JSON)<br>• تصدير إلى Excel<br>• استيراد البيانات<br>• حذف الحساب نهائياً<br>• حماية الخصوصية'
            },
            form: {
                title: '📝 إدارة البيانات',
                text: '<strong>تحكم في بياناتك.</strong><br><br>يمكنك:<br>• تصدير البيانات<br>• استيراد البيانات<br>• حذف البيانات<br>• نسخ احتياطي<br>• حماية الخصوصية'
            }
        }
    }
};

// Generic function to create resource tour
function createResourceTour(config) {
    const tour = new Shepherd.Tour({
        useModalOverlay: true,
        defaultStepOptions: {
            classes: 'shepherd-theme-custom',
            scrollTo: { behavior: 'smooth', block: 'center' },
            cancelIcon: {
                enabled: true,
                label: 'إغلاق'
            },
            modalOverlayOpeningRadius: 12,
            modalOverlayOpeningPadding: 10
        },
        tourName: config.tourName
    });

    // Step 1: Welcome message
    tour.addStep({
        id: 'welcome',
        title: config.steps.welcome.title,
        text: config.steps.welcome.text,
        buttons: [
            {
                text: 'تخطي',
                action: tour.cancel,
                secondary: true
            },
            {
                text: 'ابدأ',
                action: tour.next,
                secondary: false
            }
        ]
    });

    // Step 2: Highlight the sidebar menu
    tour.addStep({
        id: 'sidebar-location',
        title: '📍 القائمة الجانبية',
        text: '<strong>هذه هي القائمة الجانبية الرئيسية.</strong><br><br>من هنا يمكنك الوصول إلى جميع أقسام النظام.',
        attachTo: {
            element: () => document.querySelector('.fi-sidebar') || document.querySelector('[role="navigation"]') || document.querySelector('aside'),
            on: 'right'
        },
        buttons: [
            {
                text: 'السابق',
                action: tour.back,
                secondary: true
            },
            {
                text: 'التالي',
                action: tour.next,
                secondary: false
            }
        ]
    });

    // Step 3: Highlight the specific menu item
    tour.addStep({
        id: 'menu-item',
        title: config.steps.menu.title,
        text: config.steps.menu.text,
        attachTo: {
            element: () => {
                // Try multiple selectors to find the navigation item
                const selectors = [
                    `[data-tour="${config.dataTour}"]`,
                    `a[href*="${config.urlPattern.split('/')[1]}"]`
                ];
                
                for (const selector of selectors) {
                    const element = document.querySelector(selector);
                    if (element) return element;
                }
                
                // Fallback: search by text content
                const navItems = document.querySelectorAll('.fi-sidebar-item, .fi-sidebar-group');
                for (const item of navItems) {
                    if (item.textContent.includes(config.menuText)) {
                        return item;
                    }
                }
                
                return null;
            },
            on: 'right'
        },
        scrollTo: true,
        highlightClass: 'shepherd-highlight-pulse',
        buttons: [
            {
                text: 'السابق',
                action: tour.back,
                secondary: true
            },
            {
                text: 'التالي',
                action: tour.next,
                secondary: false
            }
        ],
        when: {
            show: function() {
                // Add a pulsing effect to the menu item
                const element = document.querySelector(`[data-tour="${config.dataTour}"]`);
                if (element) {
                    element.classList.add('shepherd-highlight-pulse');
                }
            },
            hide: function() {
                const element = document.querySelector(`[data-tour="${config.dataTour}"]`);
                if (element) {
                    element.classList.remove('shepherd-highlight-pulse');
                }
            }
        }
    });

    // Step 4: Show the current form
    tour.addStep({
        id: 'form',
        title: config.steps.form.title,
        text: config.steps.form.text,
        attachTo: {
            element: () => document.querySelector('form') || document.querySelector('.fi-form') || document.querySelector('[wire\\:submit]') || document.querySelector('main'),
            on: 'top'
        },
        buttons: [
            {
                text: 'السابق',
                action: tour.back,
                secondary: true
            },
            {
                text: 'فهمت!',
                action: function() {
                    tour.complete();
                    // Save that user has seen this tour
                    localStorage.setItem(config.storageKey, 'true');
                    localStorage.setItem(`${config.storageKey}-at`, new Date().toISOString());
                },
                secondary: false
            }
        ]
    });

    return tour;
}

// Export individual tour creators using the generic function
export function createUserMerchantResourceTour() {
    return createResourceTour(resourceToursConfig['user-merchants']);
}

export function createWalletResourceTour() {
    return createResourceTour(resourceToursConfig['user-merchant-wallets']);
}

export function createProductResourceTour() {
    return createResourceTour(resourceToursConfig['user-merchant-products']);
}

export function createStatementResourceTour() {
    return createResourceTour(resourceToursConfig['user-merchant-account-statements']);
}

export function createOrderResourceTour() {
    return createResourceTour(resourceToursConfig['user-merchant-orders']);
}

export function createEntryResourceTour() {
    return createResourceTour(resourceToursConfig['user-merchant-account-entries']);
}

export function createTransactionResourceTour() {
    return createResourceTour(resourceToursConfig['user-merchant-payment-transactions']);
}

export function createBudgetResourceTour() {
    return createResourceTour(resourceToursConfig['budgets']);
}

export function createBudgetCategoryResourceTour() {
    return createResourceTour(resourceToursConfig['budget-categories']);
}

export function createFinancialSettingsTour() {
    return createResourceTour(resourceToursConfig['financial-settings']);
}

export function createPersonalDataTour() {
    return createResourceTour(resourceToursConfig['personal-data']);
}

// Auto-start resource tours when on create page
function checkAndStartResourceTour() {
    const currentPath = window.location.pathname;
    
    // Check each resource configuration
    for (const [key, config] of Object.entries(resourceToursConfig)) {
        if (currentPath.includes(config.urlPattern)) {
            // Check if user has already seen this tour
            const hasSeenTour = localStorage.getItem(config.storageKey);
            
            if (!hasSeenTour) {
                // Wait for DOM to be fully loaded
                setTimeout(() => {
                    try {
                        const tour = createResourceTour(config);
                        tour.start();
                        console.log(`✅ Started tour for: ${config.tourName}`);
                    } catch (error) {
                        console.error(`Error starting ${config.tourName}:`, error);
                    }
                }, 1000); // Give time for navigation items to render
            }
            break; // Only start one tour at a time
        }
    }
}

// Listen for page navigation (SPA navigation)
document.addEventListener('DOMContentLoaded', checkAndStartResourceTour);
document.addEventListener('livewire:navigated', checkAndStartResourceTour);

// Make it globally accessible
window.ShepherdTour = {
    // Main app tour
    init: initializeShepherdTour,
    start: function() {
        try {
            const tour = initializeShepherdTour();
            tour.start();
        } catch (error) {
            console.error('Error starting tour:', error);
            throw error;
        }
    },
    
    // Resource tours - manual triggers
    startUserMerchantTour: function() {
        try {
            localStorage.removeItem('user-merchant-tour-completed');
            const tour = createUserMerchantResourceTour();
            tour.start();
        } catch (error) {
            console.error('Error starting UserMerchant tour:', error);
            throw error;
        }
    },
    
    startWalletTour: function() {
        try {
            localStorage.removeItem('wallet-tour-completed');
            const tour = createWalletResourceTour();
            tour.start();
        } catch (error) {
            console.error('Error starting Wallet tour:', error);
            throw error;
        }
    },
    
    startProductTour: function() {
        try {
            localStorage.removeItem('product-tour-completed');
            const tour = createProductResourceTour();
            tour.start();
        } catch (error) {
            console.error('Error starting Product tour:', error);
            throw error;
        }
    },
    
    startStatementTour: function() {
        try {
            localStorage.removeItem('statement-tour-completed');
            const tour = createStatementResourceTour();
            tour.start();
        } catch (error) {
            console.error('Error starting Statement tour:', error);
            throw error;
        }
    },
    
    startOrderTour: function() {
        try {
            localStorage.removeItem('order-tour-completed');
            const tour = createOrderResourceTour();
            tour.start();
        } catch (error) {
            console.error('Error starting Order tour:', error);
            throw error;
        }
    },
    
    startEntryTour: function() {
        try {
            localStorage.removeItem('entry-tour-completed');
            const tour = createEntryResourceTour();
            tour.start();
        } catch (error) {
            console.error('Error starting Entry tour:', error);
            throw error;
        }
    },
    
    startTransactionTour: function() {
        try {
            localStorage.removeItem('transaction-tour-completed');
            const tour = createTransactionResourceTour();
            tour.start();
        } catch (error) {
            console.error('Error starting Transaction tour:', error);
            throw error;
        }
    },
    
    startBudgetTour: function() {
        try {
            localStorage.removeItem('budget-tour-completed');
            const tour = createBudgetResourceTour();
            tour.start();
        } catch (error) {
            console.error('Error starting Budget tour:', error);
            throw error;
        }
    },
    
    startBudgetCategoryTour: function() {
        try {
            localStorage.removeItem('budget-category-tour-completed');
            const tour = createBudgetCategoryResourceTour();
            tour.start();
        } catch (error) {
            console.error('Error starting BudgetCategory tour:', error);
            throw error;
        }
    },
    
    startFinancialSettingsTour: function() {
        try {
            localStorage.removeItem('financial-settings-tour-completed');
            const tour = createFinancialSettingsTour();
            tour.start();
        } catch (error) {
            console.error('Error starting FinancialSettings tour:', error);
            throw error;
        }
    },
    
    startPersonalDataTour: function() {
        try {
            localStorage.removeItem('personal-data-tour-completed');
            const tour = createPersonalDataTour();
            tour.start();
        } catch (error) {
            console.error('Error starting PersonalData tour:', error);
            throw error;
        }
    },
    
    // Utility functions
    resetAllTours: function() {
        // Clear all tour completion flags
        Object.values(resourceToursConfig).forEach(config => {
            localStorage.removeItem(config.storageKey);
            localStorage.removeItem(`${config.storageKey}-at`);
        });
        console.log('✅ All resource tours have been reset!');
    },
    
    listCompletedTours: function() {
        console.log('📊 Tour Completion Status:');
        Object.entries(resourceToursConfig).forEach(([key, config]) => {
            const completed = localStorage.getItem(config.storageKey);
            const completedAt = localStorage.getItem(`${config.storageKey}-at`);
            console.log(`  ${config.menuText}: ${completed ? '✅ Completed' : '❌ Not completed'}${completedAt ? ` (${new Date(completedAt).toLocaleString('ar-SA')})` : ''}`);
        });
    }
};

// Export for use in other modules
export default initializeShepherdTour;