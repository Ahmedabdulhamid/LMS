<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="منصة تعليمية تجمع الطلاب بأفضل المدرّسين في تجربة تعلم مرنة وعملية.">
    <title>{{ config('app.name', 'EduPath') }} | ابدأ رحلة تعلمك</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    @php($ar = app()->getLocale() === 'ar')
    <header class="header" id="top">
        <div class="container nav">
            <a class="brand" href="#top"><img src="{{ asset('images/learning-platform-logo.png') }}" alt=""><b>{{
                    config('app.name', 'EduPath') }}</b></a>
            <nav><a href="#features">{{ $ar?'المميزات':'Features' }}</a><a href="#paths">{{ $ar?'مسارات
                    التعلم':'Learning paths' }}</a><a href="#steps">{{ $ar?'كيف تبدأ؟':'How it works' }}</a></nav>
            <div class="nav-actions"><a class="lang" href="{{ route('locale.switch',$ar?'en':'ar') }}">{{ $ar?'EN':'ع'
                    }}</a><a class="login" href="/students/login">{{ $ar?'تسجيل الدخول':'Sign in' }}</a><a
                    class="btn small" href="/students/register">{{ $ar?'ابدأ الآن':'Get started' }}</a><button
                    class="menu" aria-expanded="false"><i></i><i></i><i></i></button></div>
        </div>
        <div class="mobile"><a href="#features">{{ $ar?'المميزات':'Features' }}</a><a href="#paths">{{ $ar?'مسارات
                التعلم':'Learning paths' }}</a><a href="#steps">{{ $ar?'كيف تبدأ؟':'How it works' }}</a><a
                href="/students/login">{{ $ar?'دخول الطالب':'Student sign in' }}</a></div>
    </header>

    <main>
        <section class="hero">
            <div class="dots"></div>
            <div class="container hero-grid">
                <div class="hero-copy reveal"><span class="kicker">{{ $ar?'تعلّم اليوم، واصنع مستقبلك':'LEARN TODAY.
                        LEAD TOMORROW.' }}</span>
                    <h1>{{ $ar?'كل مهارة جديدة تفتح لك':'Every new skill opens' }} <em>{{ $ar?'بابًا جديدًا.':'a new
                            door.' }}</em></h1>
                    <p>{{ $ar?'تعلّم من مدرّسين خبراء، تقدّم بخطوات واضحة، وحوّل وقتك إلى معرفة حقيقية تساعدك في الدراسة
                        والعمل والحياة.':'Learn from expert instructors, follow a clear path, and turn your time into
                        practical knowledge for study, work, and life.' }}</p>
                    <div class="hero-actions"><a class="btn" href="/students/register">{{ $ar?'ابدأ التعلم
                            مجانًا':'Start learning for free' }} <b>←</b></a><a class="btn outline"
                            href="/instructors/register">{{ $ar?'انضم كمدرّس':'Join as instructor' }}</a></div>
                    <div class="trust">
                        <div class="faces"><i>م</i><i>س</i><i>ع</i><i>+</i></div>
                        <p><b>{{ $ar?'+١,٢٠٠ متعلم':'1,200+ learners' }}</b><small>{{ $ar?'بدأوا رحلتهم معنا':'started
                                their journey with us' }}</small></p>
                    </div>
                </div>
                <div class="hero-art reveal">
                    <div class="halo"></div><img src="{{ asset('images/learning-platform-logo.png') }}"
                        alt="{{ $ar?'كتاب مفتوح':'Open learning book' }}">
                    <aside class="float progress"><i>▤</i>
                        <p><small>{{ $ar?'تقدّمك الأسبوعي':'Weekly progress' }}</small><b>78%</b><span><u></u></span>
                        </p>
                    </aside>
                    <aside class="float lesson"><i>▶</i>
                        <p><small>{{ $ar?'الدرس القادم':'Next lesson' }}</small><b>{{ $ar?'أساسيات التصميم':'Design
                                basics' }}</b></p>
                    </aside>
                    <div class="badge">✓ {{ $ar?'شهادة معتمدة':'Verified certificate' }}</div>
                </div>
            </div>
            <div class="container stats reveal">
                <div><b>50+</b><span>{{ $ar?'مسار تعليمي':'Learning paths' }}</span></div>
                <div><b>30+</b><span>{{ $ar?'مدرّس خبير':'Expert instructors' }}</span></div>
                <div><b>4.9</b><span>{{ $ar?'متوسط التقييم':'Average rating' }}</span></div>
                <div><b>24/7</b><span>{{ $ar?'تعلّم في أي وقت':'Learn anytime' }}</span></div>
            </div>
        </section>

        <section class="section" id="features">
            <div class="container">
                <header class="section-head reveal"><span>{{ $ar?'تجربة تعليم مختلفة':'A DIFFERENT EXPERIENCE' }}</span>
                    <h2>{{ $ar?'كل ما تحتاجه لتتعلم بثقة':'Everything you need to learn confidently' }}</h2>
                    <p>{{ $ar?'صممنا كل تفصيلة لتبقيك متحمسًا، من أول درس وحتى تحقيق هدفك.':'Every detail keeps you
                        moving, from your first lesson to your goal.' }}</p>
                </header>
                <div class="features">
                    @foreach([
                    ['✦',$ar?'محتوى عملي ومركّز':'Practical content',$ar?'دروس قصيرة وواضحة، مع تطبيقات تساعدك على تثبيت
                    كل مهارة.':'Focused lessons with activities that make every skill stick.','gold'],
                    ['◷',$ar?'تعلّم على وقتك':'Learn on your time',$ar?'ابدأ وقتما تريد، وتابع من حيث توقفت على أي
                    جهاز.':'Start whenever you want and continue on any device.','dark'],
                    ['✓',$ar?'تابع إنجازك':'Track your progress',$ar?'لوحة بسيطة توضّح تقدمك وإنجازاتك والخطوة
                    القادمة.':'A simple dashboard shows progress and your next step.','green']] as $f)
                    <article class="feature {{ $f[3] }} reveal"><i>{{ $f[0] }}</i>
                        <h3>{{ $f[1] }}</h3>
                        <p>{{ $f[2] }}</p>
                    </article>@endforeach
                </div>
            </div>
        </section>

        <section class="section paths-bg" id="paths">
            <div class="container split reveal">
                <div><span>{{ $ar?'اختر طريقك':'CHOOSE YOUR PATH' }}</span>
                    <h2>{{ $ar?'مسارات صُممت لأهداف حقيقية':'Paths built for real goals' }}</h2>
                </div>
                <p>{{ $ar?'سواء كنت تبدأ من الصفر أو تطوّر خبرتك، ستجد مسارًا يقودك خطوة بخطوة.':'Whether starting fresh
                    or growing your expertise, there is a path to guide you.' }}</p>
            </div>
            <div class="container paths">
                @foreach([
                ['01','</>',$ar?'البرمجة والتقنية':'Code & technology',$ar?'تطوير الويب، البرمجة، وقواعد البيانات.':'Web
                development, programming, and databases.','blue'],
                ['02','✦',$ar?'التصميم والإبداع':'Design & creativity',$ar?'تصميم الواجهات، الجرافيك، وبناء الهوية.':'UI
                design, graphics, and visual identity.','yellow'],
                ['03','↗',$ar?'الأعمال والتسويق':'Business & marketing',$ar?'التسويق الرقمي، الإدارة، وريادة
                الأعمال.':'Marketing, management, and entrepreneurship.','mint']] as $p)
                <article class="path {{ $p[4] }} reveal"><small>{{ $p[0] }}</small><i>{{ $p[1] }}</i>
                    <h3>{{ $p[2] }}</h3>
                    <p>{{ $p[3] }}</p><a href="/students/register">{{ $ar?'استكشف المسار':'Explore path' }} ←</a>
                </article>@endforeach
            </div>
        </section>

        <section class="section" id="steps">
            <div class="container steps">
                <div class="steps-copy reveal"><span>{{ $ar?'ثلاث خطوات فقط':'ONLY THREE STEPS' }}</span>
                    <h2>{{ $ar?'رحلتك تبدأ الآن، والباقي علينا':'Your journey starts now—we guide the rest' }}</h2>
                    <p>{{ $ar?'تجربة بسيطة بلا تعقيد، تساعدك على التركيز في أهم شيء: التعلّم.':'A simple experience
                        focused on what matters: learning.' }}</p><a class="btn" href="/students/register">{{ $ar?'أنشئ
                        حسابك':'Create your account' }}</a>
                </div>
                <ol>
                    @foreach([[$ar?'أنشئ حسابك مجانًا':'Create your free account',$ar?'سجّل بياناتك في أقل من
                    دقيقة.':'Sign up in less than a minute.'],[$ar?'اختر المسار المناسب':'Choose the right
                    path',$ar?'حدد هدفك وابدأ بالمستوى المناسب لك.':'Set your goal and start at your
                    level.'],[$ar?'تعلّم، طبّق، وتقدّم':'Learn, practice, and grow',$ar?'أنجز الدروس وتابع تقدمك خطوة
                    بخطوة.':'Complete lessons and track every step.']] as $i=>$s)
                    <li class="reveal"><i>{{ $i+1 }}</i>
                        <div>
                            <h3>{{ $s[0] }}</h3>
                            <p>{{ $s[1] }}</p>
                        </div>
                    </li>@endforeach
                </ol>
            </div>
        </section>

        <section class="cta">
            <div class="container cta-card reveal">
                <div><span>{{ $ar?'جاهز للخطوة الأولى؟':'READY FOR YOUR FIRST STEP?' }}</span>
                    <h2>{{ $ar?'ابدأ اليوم. مستقبلك يستحق.':'Start today. Your future is worth it.' }}</h2>
                    <p>{{ $ar?'انضم إلى مجتمع من المتعلمين والمدرّسين الطموحين.':'Join a community of ambitious learners
                        and instructors.' }}</p>
                </div>
                <aside><a class="btn white" href="/students/register">{{ $ar?'سجّل كطالب':'Join as student' }}</a><a
                        class="btn clear" href="/instructors/register">{{ $ar?'سجّل كمدرّس':'Join as instructor' }}</a>
                </aside>
            </div>
        </section>
    </main>
    <footer>
        <div class="container footer"><a class="brand" href="#top"><img
                    src="{{ asset('images/learning-platform-logo.png') }}" alt=""><b>{{ config('app.name','EduPath')
                    }}</b></a>
            <p>© {{ date('Y') }} {{ config('app.name','EduPath') }}. {{ $ar?'جميع الحقوق محفوظة.':'All rights reserved.'
                }}</p>
            <div><a href="/students/login">{{ $ar?'دخول الطالب':'Student login' }}</a><a href="/instructors/login">{{
                    $ar?'دخول المدرّس':'Instructor login' }}</a></div>
        </div>
    </footer>
</body>

</html>
