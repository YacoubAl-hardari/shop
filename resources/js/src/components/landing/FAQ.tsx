import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";

const FAQ = () => {
  const faqs = [
    {
      question: "ما الفرق بين حساب التاجر وحساب العميل؟",
      answer:
        "حساب التاجر يمنحك وصولاً كاملاً لنظام نقطة البيع (POS)، إدارة المنتجات والمخزون، إصدار الفواتير، القيود المحاسبية، وشجرة الحسابات. أما حساب العميل فيركز على متابعة مديونياتك وأرصدتك لدى التجار، واستلام كشوف الحساب، ومقارنة إنفاقك، وإدارة الميزانية الشخصية.",
    },
    {
      question: "هل نقطة البيع (POS) تدعم الباركود؟",
      answer:
        "نعم، نظام POS يدعم البحث الفوري بالاسم أو رقم الباركود، كما يدعم مسح الباركود مباشرةً من كاميرا الجهاز (جوال أو كمبيوتر محمول) دون الحاجة لجهاز مسح خارجي. كما يمكنك طباعة ملصقات الباركود الحرارية للمنتجات بمقاسات 50×30mm و58×40mm.",
    },
    {
      question: "هل الفواتير متوافقة مع هيئة الزكاة والضريبة (ZATCA)؟",
      answer:
        "النظام حالياً يولّد رمز QR وفق معايير ZATCA للمرحلة الأولى (الفاتورة الضريبية المبسطة) ويظهر تلقائياً فقط للتجار الذين لديهم رقم ضريبي مسجّل في نظامهم. أما التكامل الكامل مع منصة فاتورة الحكومية (المرحلة الثانية — الإرسال الإلكتروني والتوقيع الرقمي XML) فهو قيد التطوير ضمن خارطة الطريق القادمة.",
    },
    {
      question: "كيف يعمل النظام المحاسبي؟",
      answer:
        "يوفر النظام دليل حسابات هرمي قابل للتخصيص، مع تسجيل قيود يومية يدوية وتلقائية مرتبطة بعمليات البيع والمدفوعات. يمكنك استعراض كشف الحساب وميزان المراجعة، وإغلاق السنة المالية مع نقل الأرصدة الختامية إلى السنة الجديدة تلقائياً.",
    },
    {
      question: "هل يمكن إدارة المرتجعات والاستبدال؟",
      answer:
        "نعم، النظام يدعم عمليات الإرجاع والاستبدال الكاملة والجزئية مع تحديث تلقائي للمخزون والرصيد المالي، وربط المرتجع بالفاتورة الأصلية للمراجعة والتدقيق.",
    },
    {
      question: "هل يمكن تصدير البيانات؟",
      answer:
        "نعم، يمكنك تصدير البيانات إلى صيغ Excel و JSON. كما يمكنك طباعة الفواتير والتقارير مباشرة من المتصفح دون الحاجة لأي برنامج خارجي.",
    },
    {
      question: "هل يدعم النظام صرف العملات الأجنبية؟",
      answer:
        "نعم، يحتوي نظام POS على حاسبة مدمجة لصرف العملات الأجنبية. يمكنك تحديد سعر الصرف لأي عملة وستحسب النظام الفكة الواجب ردها تلقائياً سواء كان الدفع بالريال أو بعملة أجنبية.",
    },
    {
      question: "هل البيانات آمنة؟",
      answer:
        "نعم، جميع البيانات محمية بنظام صلاحيات محكم، وكل تاجر يرى فقط بيانات عمله الخاصة. نستخدم تشفيراً متقدماً ونحتفظ بنسخ احتياطية دورية لضمان عدم فقدان أي معلومة.",
    },
  ];

  return (
    <section id="faq" className="py-20 px-4">
      <div className="container mx-auto max-w-4xl">
        {/* Section Header */}
        <div className="text-center mb-16 animate-scale-in">
          <div className="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 rounded-full text-sm font-medium text-primary mb-4">
            الأسئلة الشائعة
          </div>
          <h2 className="text-4xl lg:text-5xl font-black mb-6">
            هل لديك
            <span className="text-gradient"> أسئلة؟</span>
          </h2>
          <p className="text-xl text-muted-foreground">
            إجابات صريحة ودقيقة عن النظام وقدراته الحالية
          </p>
        </div>

        {/* FAQ Accordion */}
        <div className="animate-scale-in" style={{ animationDelay: "0.2s" }}>
          <Accordion type="single" collapsible className="space-y-4">
            {faqs.map((faq, index) => (
              <AccordionItem
                key={index}
                value={`item-${index}`}
                className="rounded-2xl px-6 border border-border bg-card dark:bg-[#1e2227] dark:border-[#2d3238] shadow-sm dark:shadow-lg"
              >
                <AccordionTrigger className="text-right hover:no-underline py-6">
                  <span className="text-lg font-bold text-foreground">{faq.question}</span>
                </AccordionTrigger>
                <AccordionContent className="text-right pb-6">
                  <p className="text-muted-foreground leading-relaxed">{faq.answer}</p>
                </AccordionContent>
              </AccordionItem>
            ))}
          </Accordion>
        </div>

        {/* Contact CTA */}
        <div className="mt-12 text-center">
          <div className="rounded-2xl p-8 border border-border bg-card dark:bg-[#1e2227] dark:border-[#2d3238] shadow-sm dark:shadow-lg">
            <h3 className="text-2xl font-bold mb-3 text-foreground">لم تجد إجابة لسؤالك؟</h3>
            <p className="text-muted-foreground mb-6">تواصل معنا وسنكون سعداء بمساعدتك</p>
            <div className="flex flex-col sm:flex-row gap-3 justify-center">
              <a
                href="mailto:info@example.com"
                className="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-primary text-primary-foreground font-medium hover:opacity-90 transition-opacity"
              >
                راسلنا عبر الإيميل
              </a>
              <a
                href="/timeline"
                className="inline-flex items-center justify-center px-6 py-3 rounded-lg border border-border font-medium hover:bg-muted/50 transition-colors text-foreground"
              >
                شاهد خارطة الطريق
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default FAQ;
