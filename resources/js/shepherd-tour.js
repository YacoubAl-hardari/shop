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
        id: 'merchant-categories',
        title: 'تصنيفات التاجر',
        text: '<br>هنا تجد قائمة كاملة بجميع تصنيفات التجار.<br><br>يمكنك:<br>• إضافة تصنيف جديد<br>• عرض تفاصيل كل تصنيف<br>• تعديل معلومات التصنيف<br>• متابعة التصنيفات المالية معه',
        attachTo: '[data-tour="merchant-categories"]',
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
        'merchant-categories': 'تصنيفات التاجر',
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


// Export for use in other modules
export default initializeShepherdTour;