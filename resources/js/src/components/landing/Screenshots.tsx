import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { 
  ChevronLeft, 
  ChevronRight, 
  BarChart3, 
  Users, 
  FileText, 
  Wallet,
  ShoppingCart,
  TrendingUp 
} from "lucide-react";
import { useState, useEffect, useRef } from "react";
import dashboardPreview from "@/assets/dashboard-preview.png";

const Screenshots = () => {
  const [activeSlide, setActiveSlide] = useState(0);
  const [isAutoPlaying, setIsAutoPlaying] = useState(true);
  const [direction, setDirection] = useState<'next' | 'prev'>('next');
  const [isTransitioning, setIsTransitioning] = useState(false);

  const screenshots = [
    {
      title: "لوحة التحكم الرئيسية",
      description: "نظرة شاملة على جميع العمليات المالية مع رسوم بيانية تفاعلية وإحصائيات فورية",
      image: dashboardPreview,
      icon: BarChart3,
      color: "from-primary to-primary-hover",
      features: ["إحصائيات فورية", "رسوم بيانية تفاعلية", "تنبيهات ذكية"]
    },
    {
      title: "إدارة التجار والعملاء",
      description: "نظام متكامل لإدارة بيانات التجار والعملاء مع سجل كامل للمعاملات",
      image: dashboardPreview,
      icon: Users,
      color: "from-secondary to-secondary-hover",
      features: ["بطاقات تعريفية", "سجل المعاملات", "تصنيف ذكي"]
    },
    {
      title: "الفواتير والطلبات",
      description: "إنشاء وإدارة الفواتير والطلبات بسهولة مع حساب تلقائي للإجماليات والضرائب",
      image: dashboardPreview,
      icon: FileText,
      color: "from-primary to-secondary",
      features: ["ترقيم تلقائي", "حساب الضرائب", "طباعة احترافية"]
    },
    {
      title: "المحافظ والمدفوعات",
      description: "إدارة شاملة للمحافظ الرقمية وطرق الدفع المختلفة مع تتبع لحظي",
      image: dashboardPreview,
      icon: Wallet,
      color: "from-secondary to-primary",
      features: ["محافظ متعددة", "تتبع لحظي", "تقارير تفصيلية"]
    },
    {
      title: "نظام المبيعات",
      description: "واجهة سريعة وسهلة لإدخال عمليات البيع مع دعم الباركود والبحث الذكي",
      image: dashboardPreview,
      icon: ShoppingCart,
      color: "from-primary to-secondary",
      features: ["بحث ذكي", "دعم الباركود", "إضافة سريعة"]
    },
    {
      title: "التقارير والتحليلات",
      description: "تقارير مالية شاملة مع إمكانية التصدير والتحليل المتقدم للأداء",
      image: dashboardPreview,
      icon: TrendingUp,
      color: "from-secondary to-primary-hover",
      features: ["تصدير متعدد", "تحليل متقدم", "رسوم بيانية"]
    }
  ];

  useEffect(() => {
    if (!isAutoPlaying) return;
    
    const interval = setInterval(() => {
      handleSlideChange('next', (activeSlide + 1) % screenshots.length);
    }, 5000);

    return () => clearInterval(interval);
  }, [isAutoPlaying, screenshots.length, activeSlide]);

  const handleSlideChange = (dir: 'next' | 'prev', newIndex: number) => {
    if (isTransitioning) return;
    
    setIsTransitioning(true);
    setDirection(dir);
    
    setTimeout(() => {
      setActiveSlide(newIndex);
      setTimeout(() => {
        setIsTransitioning(false);
      }, 100);
    }, 50);
  };

  const nextSlide = () => {
    setIsAutoPlaying(false);
    const newIndex = (activeSlide + 1) % screenshots.length;
    handleSlideChange('next', newIndex);
  };

  const prevSlide = () => {
    setIsAutoPlaying(false);
    const newIndex = (activeSlide - 1 + screenshots.length) % screenshots.length;
    handleSlideChange('prev', newIndex);
  };

  const goToSlide = (index: number) => {
    if (index === activeSlide) return;
    setIsAutoPlaying(false);
    const dir = index > activeSlide ? 'next' : 'prev';
    handleSlideChange(dir, index);
  };

  const currentScreenshot = screenshots[activeSlide];
  const Icon = currentScreenshot.icon;

  return (
    <section id="screenshots" className="py-20 relative overflow-hidden">
      {/* Background Effects */}
      <div className="absolute inset-0 bg-gradient-to-b from-background via-accent/5 to-background" />
      <div className="absolute inset-0 bg-[radial-gradient(circle_at_70%_30%,rgba(var(--primary)/0.05),transparent_70%)]" />

      <div className="container mx-auto px-4 relative">
        {/* Section Header */}
        <div className="text-center max-w-3xl mx-auto mb-16 animate-fade-in">
          <Badge className="mb-4" variant="outline">
            استكشف النظام
          </Badge>
          <h2 className="text-4xl md:text-5xl font-bold mb-6">
            واجهات <span className="text-gradient">احترافية وسهلة</span> الاستخدام
          </h2>
          <p className="text-xl text-muted-foreground">
            تصفح واجهات النظام المختلفة واكتشف كيف يمكنها تسهيل عملك اليومي
          </p>
        </div>

        {/* Main Showcase */}
        <div className="max-w-7xl mx-auto">
          <div className="grid lg:grid-cols-2 gap-8 items-center">
            {/* Left Side - Content */}
            <div className="order-2 lg:order-1">
              <div 
                className={`space-y-6 transition-all duration-700 ease-out ${
                  isTransitioning 
                    ? direction === 'next' 
                      ? 'opacity-0 translate-x-8' 
                      : 'opacity-0 -translate-x-8'
                    : 'opacity-100 translate-x-0'
                }`}
              >
                {/* Icon and Title */}
                <div className="flex items-start gap-4">
                  <div 
                    className={`p-3 rounded-xl bg-gradient-to-br ${currentScreenshot.color} flex-shrink-0 transition-all duration-500 ${
                      isTransitioning ? 'scale-90 rotate-12' : 'scale-100 rotate-0'
                    }`}
                  >
                    <Icon className="w-8 h-8 text-white" />
                  </div>
                  <div className="flex-1">
                    <h3 className="text-3xl font-bold mb-3 transition-all duration-500">
                      {currentScreenshot.title}
                    </h3>
                    <p className="text-lg text-muted-foreground leading-relaxed">
                      {currentScreenshot.description}
                    </p>
                  </div>
                </div>

                {/* Features */}
                <div className="space-y-3 pr-16">
                  {currentScreenshot.features.map((feature, idx) => (
                    <div 
                      key={`${activeSlide}-${idx}`}
                      className="flex items-center gap-3 animate-fade-in"
                      style={{ 
                        animationDelay: `${idx * 0.1}s`,
                        animationFillMode: 'both'
                      }}
                    >
                      <div className={`w-2 h-2 rounded-full bg-gradient-to-br ${currentScreenshot.color} animate-pulse`} />
                      <span className="text-foreground font-medium">{feature}</span>
                    </div>
                  ))}
                </div>

                {/* Navigation Dots */}
                <div className="flex items-center gap-3 pt-4">
                  {screenshots.map((_, idx) => (
                    <button
                      key={idx}
                      onClick={() => goToSlide(idx)}
                      disabled={isTransitioning}
                      className={`h-2 rounded-full transition-all duration-500 ease-out disabled:opacity-50 ${
                        idx === activeSlide 
                          ? 'w-12 bg-gradient-to-r ' + currentScreenshot.color + ' shadow-lg'
                          : 'w-2 bg-border hover:bg-muted-foreground/50 hover:w-6'
                      }`}
                      aria-label={`الانتقال إلى الشريحة ${idx + 1}`}
                    />
                  ))}
                </div>

                {/* Slide Counter */}
                <div className="flex items-center gap-4 text-sm text-muted-foreground">
                  <span className="font-medium">
                    {activeSlide + 1} / {screenshots.length}
                  </span>
                  <button
                    onClick={() => setIsAutoPlaying(!isAutoPlaying)}
                    className="text-xs px-3 py-1 rounded-full bg-muted hover:bg-muted-foreground/20 transition-colors"
                  >
                    {isAutoPlaying ? "إيقاف التشغيل التلقائي" : "تشغيل تلقائي"}
                  </button>
                </div>
              </div>
            </div>

            {/* Right Side - Screenshot */}
            <div className="order-1 lg:order-2 relative">
              <div className="relative group">
                {/* Navigation Buttons */}
                <button
                  onClick={prevSlide}
                  disabled={isTransitioning}
                  className="absolute left-4 top-1/2 -translate-y-1/2 z-10 p-3 rounded-full bg-background/90 backdrop-blur-sm border border-border shadow-lg opacity-0 group-hover:opacity-100 transition-all hover:scale-110 disabled:opacity-50 disabled:cursor-not-allowed"
                  aria-label="الشريحة السابقة"
                >
                  <ChevronRight className="w-5 h-5" />
                </button>
                <button
                  onClick={nextSlide}
                  disabled={isTransitioning}
                  className="absolute right-4 top-1/2 -translate-y-1/2 z-10 p-3 rounded-full bg-background/90 backdrop-blur-sm border border-border shadow-lg opacity-0 group-hover:opacity-100 transition-all hover:scale-110 disabled:opacity-50 disabled:cursor-not-allowed"
                  aria-label="الشريحة التالية"
                >
                  <ChevronLeft className="w-5 h-5" />
                </button>

                {/* Screenshot Card */}
                <Card className="relative overflow-hidden p-2 bg-gradient-to-br from-card to-accent/10 border-2 transition-all duration-500">
                  {/* Gradient Overlay with smooth transition */}
                  <div 
                    className={`absolute inset-0 bg-gradient-to-br ${currentScreenshot.color} transition-opacity duration-700`}
                    style={{ opacity: 0.1 }}
                  />
                  
                  {/* Image Container */}
                  <div className="relative rounded-lg overflow-hidden bg-background shadow-xl">
                    <div className="relative">
                      <img
                        src={currentScreenshot.image}
                        alt={currentScreenshot.title}
                        className={`w-full h-auto transition-all duration-700 ease-out ${
                          isTransitioning
                            ? direction === 'next'
                              ? 'opacity-0 scale-95 translate-x-12'
                              : 'opacity-0 scale-95 -translate-x-12'
                            : 'opacity-100 scale-100 translate-x-0'
                        }`}
                        key={activeSlide}
                      />
                      
                      {/* Smooth overlay during transition */}
                      <div 
                        className={`absolute inset-0 bg-gradient-to-br ${currentScreenshot.color} transition-opacity duration-300 pointer-events-none`}
                        style={{ 
                          opacity: isTransitioning ? 0.15 : 0 
                        }}
                      />
                    </div>
                    
                    {/* Hover Overlay Effect */}
                    <div className="absolute inset-0 bg-gradient-to-t from-background/50 via-transparent to-transparent opacity-0 hover:opacity-100 transition-opacity duration-500" />
                  </div>
                </Card>

                {/* Decorative Elements with animation */}
                <div 
                  className={`absolute -bottom-4 -right-4 w-32 h-32 bg-gradient-to-br ${currentScreenshot.color} rounded-full blur-3xl -z-10 transition-all duration-700`}
                  style={{ 
                    opacity: isTransitioning ? 0.1 : 0.2,
                    transform: isTransitioning ? 'scale(0.8)' : 'scale(1)'
                  }}
                />
                <div 
                  className={`absolute -top-4 -left-4 w-32 h-32 bg-gradient-to-br ${currentScreenshot.color} rounded-full blur-3xl -z-10 transition-all duration-700`}
                  style={{ 
                    opacity: isTransitioning ? 0.1 : 0.2,
                    transform: isTransitioning ? 'scale(0.8)' : 'scale(1)'
                  }}
                />
              </div>
            </div>
          </div>
        </div>

        {/* Bottom Thumbnails */}
        <div className="max-w-6xl mx-auto mt-12">
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            {screenshots.map((screenshot, idx) => {
              const ThumbIcon = screenshot.icon;
              return (
                <button
                  key={idx}
                  onClick={() => goToSlide(idx)}
                  disabled={isTransitioning}
                  className={`group relative p-4 rounded-xl border-2 transition-all duration-500 ease-out disabled:opacity-50 disabled:cursor-not-allowed ${
                    idx === activeSlide
                      ? 'border-primary bg-primary/5 shadow-lg scale-105'
                      : 'border-border bg-card hover:border-primary/50 hover:shadow-md hover:scale-105'
                  }`}
                >
                  <div className={`w-10 h-10 rounded-lg bg-gradient-to-br ${screenshot.color} flex items-center justify-center mb-2 transition-all duration-500 ${
                    idx === activeSlide 
                      ? 'scale-110 shadow-glow' 
                      : 'group-hover:scale-110'
                  }`}>
                    <ThumbIcon className="w-5 h-5 text-white" />
                  </div>
                  <p className={`text-xs font-medium transition-colors duration-300 ${
                    idx === activeSlide ? 'text-foreground' : 'text-muted-foreground'
                  }`}>
                    {screenshot.title}
                  </p>
                  
                  {/* Active indicator */}
                  {idx === activeSlide && (
                    <div className={`absolute -bottom-1 left-1/2 -translate-x-1/2 w-1/2 h-1 rounded-full bg-gradient-to-r ${screenshot.color} animate-pulse`} />
                  )}
                </button>
              );
            })}
          </div>
        </div>
      </div>
    </section>
  );
};

export default Screenshots;