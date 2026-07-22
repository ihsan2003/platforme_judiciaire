import re
import json

def extract_hierarchy(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # On divise le contenu par "محاكم الاستئناف" ou des marqueurs de tableaux
    # pour identifier les blocs de juridictions de second degré.
    
    # Structure cible : { "Cour d'Appel": ["TPI 1", "TPI 2", ...] }
    hierarchy = {
        "Appel": {},
        "Appel Commercial": {},
        "Appel Administratif": {}
    }

    # 1. Extraction pour le Tableau 1 (Droit commun)
    # On cherche les occurrences de noms de Cours d'Appel suivies de leurs TPI
    # Dans le format pdftotext -layout, les colonnes sont souvent alignées.
    
    # Note: L'analyse manuelle du texte extrait montre que les noms des Cours d'Appel
    # apparaissent à droite ou dans une colonne spécifique.
    
    # Liste des Cours d'Appel (23)
    cours_appel = [
        "الرباط", "القنيطرة", "الدار البيضاء", "الجديدة", "سطات", "فاس", "مكناس", 
        "تطوان", "طنجة", "وجدة", "الناظور", "الحسيمة", "بني ملال", "خريبكة", 
        "مراكش", "آسفي", "ورزازات", "أكادير", "كلميم", "العيون", "الداخلة", 
        "الرشيدية", "تازة"
    ]
    
    # On va chercher les blocs entre chaque mention de Cour d'Appel dans le tableau 1
    # Pour simplifier, on va utiliser une approche par mots-clés "المحاكم الابتدائية" 
    # et associer aux CA correspondantes.
    
    # Comme l'extraction textuelle de tableaux complexes est bruitée, 
    # je vais me baser sur les patterns observés dans le texte.
    
    current_ca = None
    lines = content.split('\n')
    
    for line in lines:
        # Détection de la Cour d'Appel (colonne de droite dans le tableau)
        for ca in cours_appel:
            if f"محكمة الاستئناف ب{ca}" in line or (ca in line and "محاكم الاستئناف" in line):
                current_ca = ca
        
        # Détection des TPI (colonne du milieu)
        # Souvent isolés ou avec des espaces autour
        tpi_match = re.search(r'المحكمة الابتدائية ب([\w\s]+)', line)
        if not tpi_match:
             # Parfois juste le nom de la ville dans la colonne du milieu
             pass 

    # Approche alternative : Extraire manuellement les blocs clés du texte pour le seeder
    # car le parsing automatique de ce PDF spécifique est risqué sans vision par cellule.
    
    # Je vais extraire les relations majeures constatées
    relations = {
        "الرباط": ["الرباط", "تمارة", "سلا", "الخميسات", "تيفلت", "الرماني"],
        "القنيطرة": ["القنيطرة", "سيدي قاسم", "مشرع بلقصري", "سيدي سليمان", "سوق الأربعاء الغرب"],
        "الدار البيضاء": ["الدار البيضاء", "المحمدية", "بنسليمان", "بوزنيقة"],
        "الجديدة": ["الجديدة", "سيدي بنور"],
        "سطات": ["سطات", "برشيد", "بن أحمد"],
        "فاس": ["فاس", "صفرو", "بولمان", "تاونات"],
        "مكناس": ["مكناس", "إفران", "الحاجب", "أزرو"],
        "تطوان": ["تطوان", "شفشاون", "وزان", "المضيق"],
        "طنجة": ["طنجة", "أصيلة", "العرائش", "القصر الكبير"],
        "وجدة": ["وجدة", "بركان", "تاوريرت", "جرادة", "فجيج"],
        "الناظور": ["الناظور", "الدريوش"],
        "الحسيمة": ["الحسيمة", "تارجيست"],
        "بني ملال": ["بني ملال", "قصبة تادلة", "أزيلال", "دمنات", "الفقيه بن صالح", "سوق السبت أولاد النمة", "خنيفرة"],
        "خريبكة": ["خريبكة", "وادي زم", "أبي الجعد"],
        "مراكش": ["مراكش", "تحناوت", "شيشاوة", "إمنتانوت", "قلعة السراغنة", "ابن جرير"],
        "آسفي": ["آسفي", "اليوسفية", "الصويرة"],
        "ورزازات": ["ورزازات", "زاكورة", "تنغير"],
        "أكادير": ["أكادير", "إنزكان", "أيت ملول", "تارودانت", "تيزنيت", "طاطا", "بيوكرى"],
        "كلميم": ["كلميم", "طانطان", "أسا الزاك", "سيدي إفني"],
        "العيون": ["العيون", "السمارة", "بوجدور"],
        "الداخلة": ["الداخلة"],
        "الرشيدية": ["الرشيدية", "أرفود", "الريش", "ميدلت"],
        "تازة": ["تازة", "جرسيف"]
    }
    
    return relations

if __name__ == "__main__":
    # Ce script est une ébauche pour guider la génération du seeder
    # Je vais plutôt procéder par une lecture ciblée du texte extrait.
    pass
