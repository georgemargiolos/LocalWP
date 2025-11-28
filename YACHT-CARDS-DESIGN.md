# Yacht Cards Design - Visual Mockup

## How Results Look

### **Results Page Layout**

```
┌─────────────────────────────────────────────────────────────┐
│                    Available Yachts                         │
│                Found 15 yachts                              │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│              🏆 YOLO Charters Fleet                         │
└─────────────────────────────────────────────────────────────┘

┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│  [YOLO] 🔴  │  │  [YOLO] 🔴  │  │  [YOLO] 🔴  │
│              │  │              │  │              │
│   ⛵ Image   │  │   ⛵ Image   │  │   ⛵ Image   │
│              │  │              │  │              │
│ Yacht Name   │  │ Yacht Name   │  │ Yacht Name   │
│ Sailing yacht│  │ Catamaran    │  │ Motor yacht  │
│              │  │              │  │              │
│ 📍 Preveza   │  │ 📍 Athens    │  │ 📍 Corfu     │
│              │  │              │  │              │
│ €2,500 EUR   │  │ €3,200 EUR   │  │ €4,100 EUR   │
│ [View Details]│  │ [View Details]│  │ [View Details]│
└──────────────┘  └──────────────┘  └──────────────┘

┌─────────────────────────────────────────────────────────────┐
│              🤝 Partner Companies                           │
└─────────────────────────────────────────────────────────────┘

┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│              │  │              │  │              │
│   ⛵ Image   │  │   ⛵ Image   │  │   ⛵ Image   │
│              │  │              │  │              │
│ Yacht Name   │  │ Yacht Name   │  │ Yacht Name   │
│ Sailing yacht│  │ Catamaran    │  │ Motor yacht  │
│              │  │              │  │              │
│ 📍 Location  │  │ 📍 Location  │  │ 📍 Location  │
│              │  │              │  │              │
│ €2,200 EUR   │  │ €2,800 EUR   │  │ €3,500 EUR   │
│ [View Details]│  │ [View Details]│  │ [View Details]│
│              │  │              │  │              │
│ Partner Co.  │  │ Partner Co.  │  │ Partner Co.  │
└──────────────┘  └──────────────┘  └──────────────┘
```

---

## **Yacht Card Structure**

### **YOLO Boat Card** (Your boats - highlighted)

```html
┌─────────────────────────────────────┐
│ ┌─────────────────────────────────┐ │ ← Red border (2px solid)
│ │         [YOLO] 🔴              │ │ ← Red badge (top-right)
│ │                                 │ │
│ │  ┌─────────────────────────┐  │ │
│ │  │                         │  │ │
│ │  │      ⛵ Yacht Image     │  │ │ ← 220px height
│ │  │    (or placeholder)     │  │ │
│ │  │                         │  │ │
│ │  └─────────────────────────┘  │ │
│ │                                 │ │
│ │  Sun Odyssey 469               │ │ ← Yacht name (blue, bold)
│ │  Sailing yacht                 │ │ ← Yacht type (gray)
│ │                                 │ │
│ │  📍 Preveza Main Port          │ │ ← Location
│ │                                 │ │
│ │  €2,500 EUR  [View Details]   │ │ ← Price (green) + Button
│ └─────────────────────────────────┘ │
└─────────────────────────────────────┘
```

### **Partner Boat Card** (Standard)

```html
┌─────────────────────────────────────┐
│ ┌─────────────────────────────────┐ │ ← Gray border (1px)
│ │                                 │ │
│ │  ┌─────────────────────────┐  │ │
│ │  │                         │  │ │
│ │  │      ⛵ Yacht Image     │  │ │
│ │  │    (or placeholder)     │  │ │
│ │  │                         │  │ │
│ │  └─────────────────────────┘  │ │
│ │                                 │ │
│ │  Lagoon 440                    │ │
│ │  Catamaran                     │ │
│ │                                 │ │
│ │  📍 Athens Marina              │ │
│ │                                 │ │
│ │  €2,200 EUR  [View Details]   │ │
│ │                                 │ │
│ │  Partner Company               │ │ ← Small gray text
│ └─────────────────────────────────┘ │
└─────────────────────────────────────┘
```

---

## **Design Features**

### **Colors**
- **YOLO Badge**: Red background (#dc2626), white text
- **YOLO Border**: Red (#dc2626)
- **Yacht Name**: Blue (#1e3a8a)
- **Price**: Green (#16a34a)
- **View Button**: Blue (#1e3a8a)
- **Background**: White cards on light gray background

### **Effects**
- ✨ **Hover Effect**: Card lifts up (translateY -4px)
- 🎨 **Shadow**: Soft shadow that increases on hover
- 🔄 **Smooth Transitions**: All animations 0.3s ease

### **Responsive Grid**
- **Desktop**: 3 cards per row
- **Tablet**: 2 cards per row
- **Mobile**: 1 card per row (full width)

### **Card Information Displayed**
1. **Image** (or placeholder sailboat emoji ⛵)
2. **Yacht Name** (bold, blue)
3. **Yacht Type** (Sailing yacht, Catamaran, etc.)
4. **Location** with pin emoji 📍
5. **Price** (large, green, bold) + Currency
6. **View Details Button** (blue, hover effect)
7. **Company Info** (only for partner boats)

---

## **Actual CSS Styling**

### **Card Container**
```css
.yolo-ys-yacht-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.yolo-ys-yacht-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
```

### **YOLO Badge**
```css
.yolo-ys-yacht-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #dc2626;
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}
```

### **Price Display**
```css
.yolo-ys-price-amount {
    font-size: 24px;
    font-weight: 700;
    color: #16a34a; /* Green */
}
```

---

## **Grid Layout**

```css
.yolo-ys-results-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
}
```

This creates a **responsive grid** that automatically adjusts:
- Wide screens: 3-4 cards per row
- Medium screens: 2 cards per row
- Mobile: 1 card per row

---

## **Example HTML Output**

```html
<div class="yolo-ys-yacht-card yolo-boat">
    <div class="yolo-ys-yacht-badge">YOLO</div>
    
    <div class="yolo-ys-yacht-image">
        <span class="yolo-ys-yacht-image-placeholder">⛵</span>
    </div>
    
    <div class="yolo-ys-yacht-info">
        <h3 class="yolo-ys-yacht-name">Sun Odyssey 469</h3>
        <p class="yolo-ys-yacht-type">Sailing yacht</p>
        
        <div class="yolo-ys-yacht-location">
            📍 Preveza Main Port
        </div>
        
        <div class="yolo-ys-yacht-price">
            <div>
                <span class="yolo-ys-price-amount">2500</span>
                <span class="yolo-ys-price-currency">EUR</span>
            </div>
            <a href="#" class="yolo-ys-view-button">View Details</a>
        </div>
    </div>
</div>
```

---

## **Visual Comparison**

### **YOLO Boat vs Partner Boat**

| Feature | YOLO Boat | Partner Boat |
|---------|-----------|--------------|
| Border | **Red (2px)** | Gray (1px) |
| Badge | **"YOLO" red badge** | None |
| Position | **First section** | Second section |
| Highlight | **Yes** | No |
| Company info | Hidden | "Partner Company" |

---

## **Mobile View**

On mobile (< 768px), cards stack vertically:

```
┌─────────────────────┐
│   [YOLO] 🔴        │
│                     │
│    ⛵ Image         │
│                     │
│  Sun Odyssey 469   │
│  Sailing yacht     │
│                     │
│  📍 Preveza        │
│                     │
│  €2,500 EUR        │
│  [View Details]    │
└─────────────────────┘

┌─────────────────────┐
│   [YOLO] 🔴        │
│                     │
│    ⛵ Image         │
│                     │
│  Lagoon 440        │
│  Catamaran         │
│                     │
│  📍 Athens         │
│                     │
│  €3,200 EUR        │
│  [View Details]    │
└─────────────────────┘
```

Each card takes full width for better mobile readability.

---

## **Summary**

✅ **Modern card design** with hover effects  
✅ **YOLO boats highlighted** with red badge and border  
✅ **Responsive grid** (3 columns → 2 → 1)  
✅ **Clean typography** with clear hierarchy  
✅ **Professional colors** (blue, red, green)  
✅ **Smooth animations** on hover  
✅ **Mobile-optimized** layout  

The design is inspired by modern booking platforms like Airbnb and Booking.com, but customized for yacht charters with your YOLO branding!
