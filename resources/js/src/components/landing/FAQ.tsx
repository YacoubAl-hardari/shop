import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";

const FAQ = () => {
  const faqs = [
    {
      question: "كيف يعمل النظام؟",
      answer: "النظام يوفر منصة متكاملة لإدارة حسابات التجار والعملاء. يمكنك إضافة التجار والمنتجات، إنشاء الطلبات والفواتير، تسجيل المدفوعات، وتلقي تنبيهات ذكية عن الحسابات. كل شيء يتم بشكل تلقائي مع قيود محاسبية دقيقة.",
    },
    {
      question: "هل يدعم النظام تعدد الفروع؟",
      answer: "نعم، النظام يدعم تعدد الفروع بشكل كامل. يمكنك إضافة فروع متعددة لكل تاجر وإدارة حساباتها بشكل منفصل أو موحد حسب احتياجك.",
    },
    {
      question: "ما هي التنبيهات الذكية المتوفرة؟",
      answer: "يوفر النظام تنبيهات عند تجاوز الحد الائتماني، اقتراب الدين من الراتب، تجاوز الميزانية الشهرية، وتنبيهات مخصصة يمكنك إعدادها حسب احتياجك.",
    },
    {
      question: "هل البيانات آمنة؟",
      answer: "نعم، نستخدم أحدث تقنيات التشفير لحماية بياناتك. جميع البيانات الحساسة محمية بتشفير متقدم ونظام صلاحيات محكم. كما نحتفظ بنسخ احتياطية دورية لضمان عدم فقدان أي معلومات.",
    },
    {
      question: "هل يمكن تصدير البيانات؟",
      answer: "بالتأكيد! يمكنك تصدير البيانات إلى صيغ Excel و JSON مع توقيع رقمي للتأكد من صحة البيانات. كما يمكنك طباعة التقارير مباشرة من النظام.",
    },
    {
      question: "ما هي طرق الدفع المدعومة؟",
      answer: "النظام يدعم جميع طرق الدفع: النقدي، التحويل البنكي، البطاقات الائتمانية، المحافظ الرقمية، والشيكات. يمكنك ربط حساباتك البنكية للتحديث التلقائي.",
    },
    {
      question: "هل يوجد دعم فني؟",
      answer: "نعم، نوفر دعم فني 24/7 عبر الدردشة المباشرة، البريد الإلكتروني، والهاتف. فريقنا جاهز دائماً لمساعدتك في أي استفسار.",
    },
    {
      question: "كم تبلغ تكلفة النظام؟",
      answer: "نوفر فترة تجريبية مجانية لمدة 30 يوماً بدون الحاجة لبطاقة ائتمانية. بعدها يمكنك اختيار الباقة المناسبة لك بأسعار تنافسية تبدأ من 99 ريال شهرياً.",
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
            إليك إجابات على أكثر الأسئلة شيوعاً عن نظامنا
          </p>
        </div>

        {/* FAQ Accordion */}
        <div className="animate-scale-in" style={{ animationDelay: "0.2s" }}>
          <Accordion type="single" collapsible className="space-y-4">
            {faqs.map((faq, index) => (
              <AccordionItem
                key={index}
                value={`item-${index}`}
                className="card-gradient rounded-2xl px-6 border border-border bg-white/80 dark:bg-gradient-to-b dark:from-[#23272b] dark:to-[#181c20] dark:bg-[#181c20]/95 dark:border-[#2d3238] shadow-sm dark:shadow-md"
              >
                <AccordionTrigger className="text-right hover:no-underline py-6">
                  <span className="text-lg font-bold">{faq.question}</span>
                </AccordionTrigger>
                <AccordionContent className="text-right pb-6">
                  <p className="text-muted-foreground leading-relaxed">
                    {faq.answer}
                  </p>
                </AccordionContent>
              </AccordionItem>
            ))}
          </Accordion>
        </div>

        {/* Contact CTA */}
        <div className="mt-12 text-center">
          <div className="card-gradient rounded-2xl p-8 border border-border bg-white/80 dark:bg-gradient-to-b dark:from-[#23272b] dark:to-[#181c20] dark:bg-[#181c20]/95 dark:border-[#2d3238] shadow-sm dark:shadow-md">
            <h3 className="text-2xl font-bold mb-3">لم تجد إجابة لسؤالك؟</h3>
            <p className="text-muted-foreground mb-6">
              تواصل معنا وسنكون سعداء بمساعدتك
            </p>
            <div className="flex flex-col sm:flex-row gap-3 justify-center">
              <a
                href="#contact"
                className="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-primary text-primary-foreground font-medium hover:opacity-90 transition-opacity"
              >
                تواصل معنا
              </a>
              <a
                href="#demo"
                className="inline-flex items-center justify-center px-6 py-3 rounded-lg border border-border font-medium hover:bg-muted/50 transition-colors"
              >
                احجز عرض توضيحي
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default FAQ;
