import { CheckCircle2, Clock, Rocket, Sparkles, ArrowLeft } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Card } from "@/components/ui/card";
import Header from "@/components/landing/Header";
import Footer from "@/components/landing/Footer";

const Timeline = () => {
  const timelineItems = [
    {
      date: "Q1 2025",
      title: "التقارير الذكية المدعومة بالذكاء الاصطناعي",
      description: "تحليل تلقائي للبيانات المالية مع توقعات ذكية وتوصيات مخصصة لتحسين الأداء المالي",
      status: "upcoming",
      features: [
        "تحليل تلقائي للأنماط المالية",
        "توقعات التدفق النقدي",
        "تنبيهات ذكية للفرص والمخاطر",
        "توصيات مخصصة لكل تاجر"
      ]
    },
    {
      date: "Q2 2025",
      title: "تطبيق الموبايل الأصلي",
      description: "تطبيق محمول كامل لنظامي iOS و Android مع دعم الإشعارات الفورية والعمل دون اتصال",
      status: "upcoming",
      features: [
        "واجهة محسنة للشاشات الصغيرة",
        "إشعارات فورية للمعاملات",
        "العمل دون اتصال بالإنترنت",
        "مسح الباركود من الكاميرا"
      ]
    },
    {
      date: "Q2 2025",
      title: "نظام الفواتير الإلكترونية",
      description: "توافق كامل مع أنظمة الفوترة الإلكترونية الحكومية ودمج مع هيئة الزكاة والضريبة",
      status: "upcoming",//development
      features: [
        "توافق مع متطلبات الفاتورة الإلكترونية",
        "دمج تلقائي مع هيئة الزكاة",
        "توقيع رقمي للفواتير",
        "أرشفة إلكترونية آمنة"
      ]
    },
    {
      date: "Q3 2025",
      title: "نظام إدارة المخزون المتقدم",
      description: "إدارة شاملة للمخزون مع تتبع الكميات والتنبيهات التلقائية وإدارة الموردين",
      status: "upcoming",
      features: [
        "تتبع مخزون متعدد المواقع",
        "تنبيهات نقص المخزون",
        "إدارة الموردين والمشتريات",
        "تقارير حركة المخزون"
      ]
    },
    {
      date: "Q3 2025",
      title: "البوابة الإلكترونية للعملاء",
      description: "بوابة مخصصة تمكن العملاء من الاطلاع على حساباتهم وطلباتهم والدفع الإلكتروني",
      status: "upcoming",
      features: [
        "عرض الحساب والمعاملات",
        "طلب المنتجات أونلاين",
        "الدفع الإلكتروني الآمن",
        "تحميل الفواتير والتقارير"
      ]
    },
    {
      date: "Q4 2025",
      title: "التكامل مع الأنظمة المحاسبية",
      description: "ربط سلس مع أشهر الأنظمة المحاسبية العالمية والمحلية لتبادل البيانات تلقائياً",
      status: "upcoming",
      features: [
        "تكامل مع QuickBooks و Xero",
        "ربط مع الأنظمة المحاسبية السعودية",
        "مزامنة تلقائية للبيانات",
        "تصدير واستيراد آلي"
      ]
    }
  ];

  const getStatusBadge = (status: string) => {
    switch (status) {
      case "launched":
        return (
          <Badge className="bg-success text-success-foreground">
            <CheckCircle2 className="w-3 h-3 ml-1" />
            تم الإطلاق
          </Badge>
        );
      case "development":
        return (
          <Badge className="bg-primary text-primary-foreground">
            <Rocket className="w-3 h-3 ml-1" />
            قيد التطوير
          </Badge>
        );
      default:
        return (
          <Badge variant="outline">
            <Clock className="w-3 h-3 ml-1" />
            قريباً
          </Badge>
        );
    }
  };

  return (
    <>
      <Header />
      <div className="min-h-screen bg-background pt-16">
      {/* Hero Section */}
      <section className="relative py-20 bg-gradient-hero overflow-hidden">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.1),transparent_50%)]" />
        <div className="container mx-auto px-4 relative">
          <div className="text-center max-w-3xl mx-auto">
            <div className="inline-flex items-center justify-center p-2 bg-white/10 rounded-full mb-6 backdrop-blur-sm">
              <Sparkles className="w-6 h-6 text-white ml-2" />
              <span className="text-white font-medium px-3">خارطة الطريق</span>
            </div>
            <h1 className="text-4xl md:text-5xl font-bold text-white mb-6">
              الميزات القادمة والتحديثات
            </h1>
            <p className="text-xl text-white/90 leading-relaxed">
              نعمل باستمرار على تطوير النظام وإضافة ميزات جديدة لتلبية احتياجاتكم المتطورة
            </p>
          </div>
        </div>
      </section>

      {/* Timeline Section */}
      <section className="py-20">
        <div className="container mx-auto px-4">
          <div className="max-w-4xl mx-auto">
            {/* Timeline */}
            <div className="relative">
              {/* Timeline Line */}
              <div className="hidden md:block absolute right-[50%] top-0 bottom-0 w-0.5 bg-gradient-to-b from-primary via-secondary to-primary/20" />

              {/* Timeline Items */}
              <div className="space-y-12">
                {timelineItems.map((item, index) => (
                  <div
                    key={index}
                    className="relative animate-fade-in"
                    style={{ animationDelay: `${index * 0.1}s` }}
                  >
                    {/* Timeline Dot */}
                    <div className="hidden md:block absolute right-[50%] top-8 w-4 h-4 -mr-2 rounded-full bg-gradient-primary border-4 border-background shadow-glow" />

                    {/* Content Card */}
                    <div className={`md:w-[calc(50%-2rem)] ${index % 2 === 0 ? 'md:mr-auto md:pl-12' : 'md:ml-auto md:pr-12'}`}>
                      <Card className="p-6 hover-lift">
                        {/* Header */}
                        <div className="flex items-start justify-between mb-4">
                          <div>
                            <p className="text-sm text-muted-foreground mb-2">{item.date}</p>
                            <h3 className="text-xl font-bold text-foreground mb-2">
                              {item.title}
                            </h3>
                          </div>
                          {getStatusBadge(item.status)}
                        </div>

                        {/* Description */}
                        <p className="text-muted-foreground mb-4 leading-relaxed">
                          {item.description}
                        </p>

                        {/* Features List */}
                        <div className="space-y-2">
                          {item.features.map((feature, idx) => (
                            <div key={idx} className="flex items-start gap-2">
                              <CheckCircle2 className="w-5 h-5 text-secondary mt-0.5 flex-shrink-0" />
                              <span className="text-sm text-foreground">{feature}</span>
                            </div>
                          ))}
                        </div>
                      </Card>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* CTA */}
            <div className="mt-16 text-center">
              <Card className="p-8 bg-gradient-card">
                <Sparkles className="w-12 h-12 text-primary mx-auto mb-4" />
                <h3 className="text-2xl font-bold mb-3">
                  هل لديك اقتراح لميزة جديدة؟
                </h3>
                <p className="text-muted-foreground mb-6">
                  نحن نستمع لآرائكم ومقترحاتكم لتحسين النظام باستمرار
                </p>
                <a
                  href="#contact"
                  className="inline-flex items-center gap-2 px-6 py-3 bg-gradient-primary text-white rounded-lg font-medium hover:shadow-glow transition-all"
                >
                  تواصل معنا
                  <ArrowLeft className="w-4 h-4" />
                </a>
              </Card>
            </div>
          </div>
        </div>
      </section>
    </div>
    <Footer />
  </>
  );
};

export default Timeline;
