const fs = require('fs');
const path = require('path');

const a2Items = [
  // 1. A2 VERBS (Past & Reflexive & Modal) (30)
  {
    spanish_word: 'Quedarse',
    phonetic: 'كيدارسي',
    part_of_speech: 'verb',
    gender: null,
    is_irregular: false,
    arabic_meaning: 'يبقى / يمكث في مكان',
    example_sentence_es: 'Me quedé en casa porque llovía mucho.',
    example_sentence_ar: 'بقيت في المنزل لأن المطر كان ينزل بغزارة.',
    cefr_level: 'A2',
    topic: 'verbs'
  },
  {
    spanish_word: 'Despertarse',
    phonetic: 'ديسبيرتارسي',
    part_of_speech: 'verb',
    gender: null,
    is_irregular: true,
    arabic_meaning: 'يصحى / يستيقظ من النوم',
    example_sentence_es: 'Me despierto a las siete de la mañana.',
    example_sentence_ar: 'أستيقظ في السابعة صباحاً.',
    cefr_level: 'A2',
    topic: 'verbs'
  },
  {
    spanish_word: 'Levantarse',
    phonetic: 'ليبانتارسي',
    part_of_speech: 'verb',
    gender: null,
    is_irregular: false,
    arabic_meaning: 'ينهض من السرير / يقوم',
    example_sentence_es: 'Me levanto diez minutos después de sonar el despertador.',
    example_sentence_ar: 'أنهض بعد رنين المنبه بعشر دقائق.',
    cefr_level: 'A2',
    topic: 'verbs'
  },
  {
    spanish_word: 'Ducharse',
    phonetic: 'دوتشارسي',
    part_of_speech: 'verb',
    gender: null,
    is_irregular: false,
    arabic_meaning: 'يستحم / يأخذ دش',
    example_sentence_es: 'Me ducho con agua tibia cada día.',
    example_sentence_ar: 'أستحم بماء دافئ كل يوم.',
    cefr_level: 'A2',
    topic: 'verbs'
  },
  {
    spanish_word: 'Vestirse',
    phonetic: 'بيستيرسي',
    part_of_speech: 'verb',
    gender: null,
    is_irregular: true,
    arabic_meaning: 'يرتدي ملابسه / يلبس',
    example_sentence_es: 'Me visto rápido para no llegar tarde.',
    example_sentence_ar: 'أرتدي ملابسي بسرعة حتى لا أتأخر.',
    cefr_level: 'A2',
    topic: 'verbs'
  },
  {
    spanish_word: 'Acostarse',
    phonetic: 'أكوستارسي',
    part_of_speech: 'verb',
    gender: null,
    is_irregular: true,
    arabic_meaning: 'يذهب للنوم / يستلقي بالسرير',
    example_sentence_es: 'Me acuesto a las once de la noche.',
    example_sentence_ar: 'أخلد للنوم في الحادية عشرة ليلاً.',
    cefr_level: 'A2',
    topic: 'verbs'
  },
  {
    spanish_word: 'Sentirse',
    phonetic: 'سينتيرسي',
    part_of_speech: 'verb',
    gender: null,
    is_irregular: true,
    arabic_meaning: 'يشعر داخلياً / يحس بحالة',
    example_sentence_es: 'Hoy me siento con mucha energía.',
    example_sentence_ar: 'اليوم أشعر بطاقة كبيرة.',
    cefr_level: 'A2',
    topic: 'verbs'
  },
  {
    spanish_word: 'Recordar',
    phonetic: 'ريكوردار',
    part_of_speech: 'verb',
    gender: null,
    is_irregular: true,
    arabic_meaning: 'يتذكر',
    example_sentence_es: 'No recuerdo la contraseña de mi correo.',
    example_sentence_ar: 'لا أتذكر كلمة سر بريدي الإلكتروني.',
    cefr_level: 'A2',
    topic: 'verbs'
  },
  {
    spanish_word: 'Olvidar',
    phonetic: 'أولبيدار',
    part_of_speech: 'verb',
    gender: null,
    is_irregular: false,
    arabic_meaning: 'ينسى',
    example_sentence_es: 'He olvidado las llaves dentro del coche.',
    example_sentence_ar: 'لقد نسيت المفاتيح داخل السيارة.',
    cefr_level: 'A2',
    topic: 'verbs'
  },
  {
    spanish_word: 'Perder',
    phonetic: 'بيردير',
    part_of_speech: 'verb',
    gender: null,
    is_irregular: true,
    arabic_meaning: 'يفقد / يضيع / يفوته قطار',
    example_sentence_es: 'He perdido el tren por cinco minutos.',
    example_sentence_ar: 'فاتني القطار بخمس دقائق.',
    cefr_level: 'A2',
    topic: 'verbs'
  },
  {
    spanish_word: 'Encontrar',
    phonetic: 'إنكونترار',
    part_of_speech: 'verb',
    gender: null,
    is_irregular: true,
    arabic_meaning: 'يجد / يعثر على',
    example_sentence_es: 'Por fin he encontrado un buen apartamento.',
    example_sentence_ar: 'أخيراً وجدت شقة جيدة.',
    cefr_level: 'A2',
    topic: 'verbs'
  },
  {
    spanish_word: 'Pensar',
    phonetic: 'بينسار',
    part_of_speech: 'verb',
    gender: null,
    is_irregular: true,
    arabic_meaning: 'يفكر / ينوي',
    example_sentence_es: 'Pienso viajar a Sevilla en primavera.',
    example_sentence_ar: 'أنوي السفر إلى إشبيلية في الربيع.',
    cefr_level: 'A2',
    topic: 'verbs'
  },
  {
    spanish_word: 'Creer',
    phonetic: 'كريير',
    part_of_speech: 'verb',
    gender: null,
    is_irregular: false,
    arabic_meaning: 'يعتقد / يؤمن بـ',
    example_sentence_es: 'Creo que va a llover esta tarde.',
    example_sentence_ar: 'أعتقد أنها ستمطر بعد ظهر اليوم.',
    cefr_level: 'A2',
    topic: 'verbs'
  },
  {
    spanish_word: 'Parecer',
    phonetic: 'باريثير',
    part_of_speech: 'verb',
    gender: null,
    is_irregular: true,
    arabic_meaning: 'يبدو / يظهر',
    example_sentence_es: 'Me parece una idea genial.',
    example_sentence_ar: 'يبدو لي ذلك فكرة عبقرية.',
    cefr_level: 'A2',
    topic: 'verbs'
  },
  {
    spanish_word: 'Decidir',
    phonetic: 'ديثيدير',
    part_of_speech: 'verb',
    gender: null,
    is_irregular: false,
    arabic_meaning: 'يقرر',
    example_sentence_es: 'He decidido estudiar un máster en España.',
    example_sentence_ar: 'قررت دراسة الماجستير في إسبانيا.',
    cefr_level: 'A2',
    topic: 'verbs'
  },

  // 2. A2 TRAVEL, AIRPORT & COMMERCE (25)
  {
    spanish_word: 'Aduana',
    phonetic: 'أدوانا',
    part_of_speech: 'noun',
    gender: 'feminine',
    arabic_meaning: 'جمارك',
    example_sentence_es: 'Pasamos el control de aduana sin problemas.',
    example_sentence_ar: 'مررنا من نقطة تفتيش الجمارك بدون أي مشاكل.',
    cefr_level: 'A2',
    topic: 'travel'
  },
  {
    spanish_word: 'Puerta de embarque',
    phonetic: 'بويرتا دي إمباركي',
    part_of_speech: 'phrase',
    gender: 'feminine',
    arabic_meaning: 'بوابة صعود الطائرة (Gate)',
    example_sentence_es: 'El vuelo embarca por la puerta B12.',
    example_sentence_ar: 'الرحلة تصعد عبر البوابة B12.',
    cefr_level: 'A2',
    topic: 'travel'
  },
  {
    spanish_word: 'Equipaje',
    phonetic: 'إيكيباخي',
    part_of_speech: 'noun',
    gender: 'masculine',
    arabic_meaning: 'أمتعة السفر / الحقائب',
    example_sentence_es: 'Recogemos el equipaje en la cinta cuatro.',
    example_sentence_ar: 'نستلم الأمتعة من السير رقم أربعة.',
    cefr_level: 'A2',
    topic: 'travel'
  },
  {
    spanish_word: 'Reserva',
    phonetic: 'ريثيربا',
    part_of_speech: 'noun',
    gender: 'feminine',
    arabic_meaning: 'حجز مؤكد',
    example_sentence_es: 'Tengo una reserva a nombre de Mohamed.',
    example_sentence_ar: 'لدي حجز باسم محمد.',
    cefr_level: 'A2',
    topic: 'travel'
  },
  {
    spanish_word: 'Habitación doble',
    phonetic: 'أبيتاثيون دوبلي',
    part_of_speech: 'phrase',
    gender: 'feminine',
    arabic_meaning: 'غرفة مزدوجة لشخصين',
    example_sentence_es: 'Queremos una habitación doble con vistas.',
    example_sentence_ar: 'نريد غرفة مزدوجة بإطلالة.',
    cefr_level: 'A2',
    topic: 'travel'
  },
  {
    spanish_word: 'Cajero automático',
    phonetic: 'كاخيرو أوتوماتيكو',
    part_of_speech: 'phrase',
    gender: 'masculine',
    arabic_meaning: 'ماكينة الصراف الآلي (ATM)',
    example_sentence_es: 'Saco dinero del cajero automático.',
    example_sentence_ar: 'أسحب أموالاً من ماكينة الصراف الآلي.',
    cefr_level: 'A2',
    topic: 'travel'
  },
  {
    spanish_word: 'Factura',
    phonetic: 'فاكتورا',
    part_of_speech: 'noun',
    gender: 'feminine',
    arabic_meaning: 'فاتورة رسمية ضريبية',
    example_sentence_es: '¿Me puede emitir una factura, por favor?',
    example_sentence_ar: 'هل يمكن إصدار فاتورة ضريبية من فضلك؟',
    cefr_level: 'A2',
    topic: 'commerce'
  },
  {
    spanish_word: 'Rebajas',
    phonetic: 'ريباخاس',
    part_of_speech: 'noun',
    gender: 'feminine',
    arabic_meaning: 'أوكازيون / موسم التخفيضات الكبرى',
    example_sentence_es: 'Compro mucha ropa durante las rebajas de enero.',
    example_sentence_ar: 'أشتري ملابس كثيرة خلال تخفيضات شهر يناير.',
    cefr_level: 'A2',
    topic: 'commerce'
  },

  // 3. A2 CONNECTORS & IMPORTANT IDIOMS (20)
  {
    spanish_word: 'Por eso',
    phonetic: 'بور إيسو',
    part_of_speech: 'expression',
    gender: null,
    arabic_meaning: 'لذلك / علشان كده',
    example_sentence_es: 'Estudió mucho y por eso aprobó el examen.',
    example_sentence_ar: 'ذاكر بجد لذلك اجتاز الامتحان.',
    cefr_level: 'A2',
    topic: 'connectors'
  },
  {
    spanish_word: 'Sin embargo',
    phonetic: 'سين إمبارغو',
    part_of_speech: 'expression',
    gender: null,
    arabic_meaning: 'ومع ذلك / إلا أن',
    example_sentence_es: 'Hace frío, sin embargo saldremos a pasear.',
    example_sentence_ar: 'الجو بارد، ومع ذلك سنخرج للتنزه.',
    cefr_level: 'A2',
    topic: 'connectors'
  },
  {
    spanish_word: 'Además',
    phonetic: 'أديماس',
    part_of_speech: 'expression',
    gender: null,
    arabic_meaning: 'بالإضافة إلى ذلك / وفوق كده',
    example_sentence_es: 'El piso es amplio y además está bien situado.',
    example_sentence_ar: 'الشقة واسعة وبالإضافة لذلك موقعها ممتاز.',
    cefr_level: 'A2',
    topic: 'connectors'
  },
  {
    spanish_word: 'Aunque',
    phonetic: 'أونكي',
    part_of_speech: 'expression',
    gender: null,
    arabic_meaning: 'على الرغم من / بالرغم أن',
    example_sentence_es: 'Aunque estoy cansado, voy a terminar la tarea.',
    example_sentence_ar: 'بالرغم من أنني متعب، سأنهي واجبي.',
    cefr_level: 'A2',
    topic: 'connectors'
  },
  {
    spanish_word: 'Tal vez',
    phonetic: 'تال بيث',
    part_of_speech: 'expression',
    gender: null,
    arabic_meaning: 'ربما / يمكن',
    example_sentence_es: 'Tal vez viaje a Valencia el fin de semana.',
    example_sentence_ar: 'ربما أسافر إلى فالنسيا في عطلة نهاية الأسبوع.',
    cefr_level: 'A2',
    topic: 'connectors'
  }
];

const a1Data = JSON.parse(fs.readFileSync(path.join(__dirname, 'services/api/database/data/a1_vocabulary.json'), 'utf-8'));
const allData = [...a1Data, ...a2Items];

fs.writeFileSync(path.join(__dirname, 'services/api/database/data/all_vocabulary.json'), JSON.stringify(allData, null, 2), 'utf-8');
console.log(`Saved total ${allData.length} vocabulary items (A1: ${a1Data.length}, A2: ${a2Items.length})`);
