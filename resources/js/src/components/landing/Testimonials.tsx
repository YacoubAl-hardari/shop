import { Star, Quote } from "lucide-react";
import { User2, User, Laptop2 } from "lucide-react";

const Testimonials = () => {
  const testimonials = [
    {
      name: "أحمد محمد",
      role: "صاحب سلسلة محلات تجارية",
      image: "👨‍💼",
      rating: 5,
      text: "النظام غير حياتي التجارية تماماً! أصبح بإمكاني متابعة جميع فروعي وحساباتي بكل سهولة. التقارير المالية دقيقة والواجهة سهلة جداً.",
    },
    {
      name: "فاطمة السعيد",
      role: "مديرة مالية",
      image: "👩‍💼",
      rating: 5,
      text: "أفضل نظام محاسبي استخدمته على الإطلاق. التنبيهات الذكية ساعدتني كثيراً في إدارة الديون والميزانية. أنصح به بشدة!",
    },
    {
      name: "خالد العتيبي",
      role: "تاجر جملة",
      image: "👨‍💻",
      rating: 5,
      text: "الدعم الفني ممتاز والنظام سريع جداً. أستطيع إصدار الفواتير وتسجيل المدفوعات بثوانٍ معدودة. وفر علي الكثير من الوقت والجهد.",
    },
    {
      name: "سارة الأحمد",
      role: "صاحبة متجر إلكتروني",
      image: "👩‍💼",
      rating: 5,
      text: "التكامل مع العمليات التجارية رائع. النظام يدير المخزون والحسابات تلقائياً. أصبحت أركز أكثر على تطوير عملي بدلاً من الحسابات اليدوية.",
    },
    {
      name: "محمد العلي",
      role: "محاسب قانوني",
      image: "👨‍💼",
      rating: 5,
      text: "كمحاسب محترف، أقدر الدقة المحاسبية العالية للنظام. القيود التلقائية وكشوف الحسابات توفر علي ساعات من العمل اليومي.",
    },
    {
      name: "نورة القحطاني",
      role: "مالكة مطعم",
      image: "👩‍💼",
      rating: 5,
      text: "النظام مثالي لإدارة الموردين والحسابات. التقارير التفصيلية تساعدني على اتخاذ قرارات مالية صحيحة. تجربة رائعة!",
    },
  ];

  return (
    <section id="testimonials" className="py-20 px-4 bg-muted/30">
      <div className="container mx-auto">
        {/* Section Header */}
        <div className="text-center max-w-3xl mx-auto mb-16 animate-scale-in">
          <div className="inline-flex items-center gap-2 px-4 py-2 bg-amber-500/10 rounded-full text-sm font-medium text-amber-500 mb-4">
            آراء العملاء
          </div>
          <h2 className="text-4xl lg:text-5xl font-black mb-6">
            ماذا يقول
            <span className="text-gradient"> عملاؤنا</span>
          </h2>
          <p className="text-xl text-muted-foreground">
            تجارب حقيقية من مستخدمين راضين عن خدماتنا
          </p>
        </div>

        {/* Testimonials Grid */}
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          {testimonials.map((testimonial, index) => (
            <div
              key={index}
              className="card-gradient rounded-2xl p-6 border border-border hover-lift animate-scale-in relative bg-white/80 dark:bg-gradient-to-b dark:from-[#23272b] dark:to-[#181c20] dark:bg-[#181c20]/95 dark:border-[#2d3238] dark:shadow-2xl"
              style={{ animationDelay: `${index * 0.1}s` }}
            >
              {/* Quote Icon */}
              <div className="absolute top-4 left-4 opacity-10">
                <Quote className="w-12 h-12" />
              </div>

              {/* Testimonial Text */}
              <p className="text-sm text-foreground leading-relaxed mb-6 relative z-10">
                "{testimonial.text}"
              </p>

              {/* Author Info */}
              <div className="flex items-center gap-3">
                <div
                  className="w-14 h-14 rounded-full flex items-center justify-center shadow-md border-2 border-primary/20 bg-[linear-gradient(135deg,_hsl(220,13%,60%)_0%,_hsl(220,13%,48%)_100%)] dark:bg-[linear-gradient(135deg,_#23272b_0%,_#181c20_100%)]"
                >
                  {testimonial.image === "👨‍💼" && <User2 className="w-8 h-8 text-white drop-shadow" />}
                  {testimonial.image === "👩‍💼" && <User className="w-8 h-8 text-white drop-shadow" />}
                  {testimonial.image === "👨‍💻" && <Laptop2 className="w-8 h-8 text-white drop-shadow" />}
                </div>
                <div>
                  <div className="font-bold text-sm">{testimonial.name}</div>
                  <div className="text-xs text-muted-foreground">{testimonial.role}</div>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Trust Score */}
        <div className="mt-16 text-center">
          <div className="inline-flex flex-col items-center gap-3 bg-card rounded-2xl p-8 shadow-lg border border-border">
            <div className="flex gap-1">
              {[...Array(5)].map((_, i) => (
                <Star key={i} className="w-8 h-8 fill-amber-400 text-amber-400" />
              ))}
            </div>
            <div>
              <div className="text-3xl font-black text-gradient">4.9/5</div>
              <div className="text-sm text-muted-foreground">بناءً على +500 تقييم</div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Testimonials;
