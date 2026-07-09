-- =========================
-- CREATE DATABASE
-- =========================
CREATE DATABASE IF NOT EXISTS rag_db;
USE rag_db;

-- =========================
-- USER
-- =========================
CREATE TABLE IF NOT EXISTS User (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(255) NOT NULL,
    Email VARCHAR(255) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Image VARCHAR(255),
    Role ENUM('Admin', 'Customer', 'KitchenOwner', 'Caterer', 'DeliveryAgent') NOT NULL,
    Wallet_balance DECIMAL(10,2) DEFAULT 0.00,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS UserPhone (
    UserID INT,
    PhoneNumber VARCHAR(20),
    PRIMARY KEY (UserID, PhoneNumber),
    FOREIGN KEY (UserID) REFERENCES User(UserID) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS UserAddress (
    UserID INT,
    Address VARCHAR(255),
    PRIMARY KEY (UserID, Address),
    FOREIGN KEY (UserID) REFERENCES User(UserID) ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================
-- ROLES
-- =========================
CREATE TABLE IF NOT EXISTS Admin (
    AdminID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    FOREIGN KEY (UserID) REFERENCES User(UserID) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS Customer (
    CustomerID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    WalletBalance DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (UserID) REFERENCES User(UserID) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS KitchenOwner (
    KitchenOwnerID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    KitchenName VARCHAR(255),
    Description TEXT,
    Status ENUM('Active','Inactive','Suspended') DEFAULT 'Inactive',
    VerifyStatus ENUM('Pending','Verified','Rejected') DEFAULT 'Pending',
    Attachment VARCHAR(255),
    FOREIGN KEY (UserID) REFERENCES User(UserID) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS Caterer (
    CatererID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    BusinessName VARCHAR(255),
    Description TEXT,
    Attachment VARCHAR(255),
    IsActive BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (UserID) REFERENCES User(UserID) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS DeliveryAgent (
    DeliveryAgentID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    VehicleType VARCHAR(50),
    PlateNumber VARCHAR(50),
    Status ENUM('Available','Busy','Offline') DEFAULT 'Offline',
    Attachment VARCHAR(255),
    FOREIGN KEY (UserID) REFERENCES User(UserID) ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================
-- PAYMENT & CATEGORY
-- =========================
CREATE TABLE IF NOT EXISTS Payment (
    PaymentID INT AUTO_INCREMENT PRIMARY KEY,
    Method ENUM('Cash','Card','Wallet','Online') NOT NULL
);

CREATE TABLE IF NOT EXISTS Category (
    CategoryID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Description TEXT,
    Status ENUM('Active','Inactive') DEFAULT 'Active'
);

-- =========================
-- MENU
-- =========================
CREATE TABLE IF NOT EXISTS MenuItem (
    MenuItemID INT AUTO_INCREMENT PRIMARY KEY,
    CategoryID INT,
    KitchenOwnerID INT,
    CatererID INT,
    ItemName VARCHAR(255) NOT NULL,
    Description TEXT,
    ItemPrice DECIMAL(10,2) NOT NULL,
    Status ENUM('Available','Unavailable') DEFAULT 'Available',
    FOREIGN KEY (CategoryID) REFERENCES Category(CategoryID) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (KitchenOwnerID) REFERENCES KitchenOwner(KitchenOwnerID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (CatererID) REFERENCES Caterer(CatererID) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS ItemImage (
    MenuItemID INT,
    Image VARCHAR(255),
    PRIMARY KEY (MenuItemID, Image),
    FOREIGN KEY (MenuItemID) REFERENCES MenuItem(MenuItemID) ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================
-- SUBSCRIPTION
-- =========================
CREATE TABLE IF NOT EXISTS Subscription (
    SubscriptionID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT,
    PlanTime VARCHAR(50),
    Status ENUM('Active','Expired','Cancelled') DEFAULT 'Active',
    Price DECIMAL(10,2),
    StartDate DATE,
    EndDate DATE,
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS SubscriptionPayment (
    PaymentID INT,
    SubscriptionID INT,
    PRIMARY KEY (PaymentID, SubscriptionID),
    FOREIGN KEY (PaymentID) REFERENCES Payment(PaymentID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (SubscriptionID) REFERENCES Subscription(SubscriptionID) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS MenuSubscribe (
    SubscriptionID INT,
    MenuItemID INT,
    PRIMARY KEY (SubscriptionID, MenuItemID),
    FOREIGN KEY (SubscriptionID) REFERENCES Subscription(SubscriptionID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (MenuItemID) REFERENCES MenuItem(MenuItemID) ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================
-- LIVE CHAT & ADS
-- =========================
CREATE TABLE IF NOT EXISTS LiveChat (
    LiveChatID INT AUTO_INCREMENT PRIMARY KEY,
    SenderID INT,
    ReceiverID INT,
    Message TEXT,
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (SenderID) REFERENCES User(UserID) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (ReceiverID) REFERENCES User(UserID) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS Advertising (
    AdvertisingID INT AUTO_INCREMENT PRIMARY KEY,
    PaymentID INT,
    KitchenOwnerID INT,
    CatererID INT,
    Title VARCHAR(255),
    Description TEXT,
    StartDate DATE,
    EndDate DATE,
    Status ENUM('Active','Inactive') DEFAULT 'Active',
    FOREIGN KEY (PaymentID) REFERENCES Payment(PaymentID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (KitchenOwnerID) REFERENCES KitchenOwner(KitchenOwnerID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (CatererID) REFERENCES Caterer(CatererID) ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================
-- ORDERS
-- =========================
CREATE TABLE IF NOT EXISTS `Order` (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT,
    DeliveryAgentID INT,
    PaymentID INT,
    LiveChatID INT,
    Deposit DECIMAL(10,2) DEFAULT 0.00,
    TotalPrice DECIMAL(10,2) NOT NULL,
    LoyaltyPoints INT DEFAULT 0,
    Amount DECIMAL(10,2),
    UnitPrice DECIMAL(10,2),
    OrderStatus ENUM('Pending','Confirmed','Preparing','Ready','Delivering','Delivered','Cancelled') DEFAULT 'Pending',
    SpecialRequests TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (DeliveryAgentID) REFERENCES DeliveryAgent(DeliveryAgentID) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (LiveChatID) REFERENCES LiveChat(LiveChatID) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (PaymentID) REFERENCES Payment(PaymentID) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS MenuOrderItem (
    MenuItemID INT,
    OrderID INT,
    Quantity INT DEFAULT 1,
    PRIMARY KEY (MenuItemID, OrderID),
    FOREIGN KEY (MenuItemID) REFERENCES MenuItem(MenuItemID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (OrderID) REFERENCES `Order`(OrderID) ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================
-- REVIEWS & RATINGS
-- =========================
CREATE TABLE IF NOT EXISTS Review (
    ReviewID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT,
    KitchenOwnerID INT,
    CatererID INT,
    OrderID INT,
    Rating TINYINT CHECK (Rating BETWEEN 1 AND 5),
    Comment TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (KitchenOwnerID) REFERENCES KitchenOwner(KitchenOwnerID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (CatererID) REFERENCES Caterer(CatererID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (OrderID) REFERENCES `Order`(OrderID) ON DELETE SET NULL ON UPDATE CASCADE
);

-- =========================
-- NOTIFICATIONS
-- =========================
CREATE TABLE IF NOT EXISTS Notification (
    NotificationID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT,
    Title VARCHAR(255),
    Message TEXT,
    IsRead BOOLEAN DEFAULT FALSE,
    Type ENUM('Order','Promotion','System','Chat') DEFAULT 'System',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES User(UserID) ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================
-- CATERING REQUESTS
-- =========================
CREATE TABLE IF NOT EXISTS CateringRequest (
    RequestID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT,
    CatererID INT,
    EventType VARCHAR(100),
    EventDate DATE,
    GuestCount INT,
    Budget DECIMAL(10,2),
    Details TEXT,
    Status ENUM('Pending','Accepted','Rejected','Completed','Cancelled') DEFAULT 'Pending',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (CatererID) REFERENCES Caterer(CatererID) ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================
-- LOYALTY POINTS
-- =========================
CREATE TABLE IF NOT EXISTS LoyaltyTransaction (
    TransactionID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerID INT,
    Points INT NOT NULL,
    Type ENUM('Earned','Redeemed','Bonus','Referral') DEFAULT 'Earned',
    Description VARCHAR(255),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID) ON DELETE CASCADE ON UPDATE CASCADE
);


-- ============================================================
-- ======================== SEED DATA =========================
-- ============================================================

-- =========================
-- USERS (30 users across all roles)
-- =========================
INSERT INTO User (FullName, Email, Password, Role, Image, Wallet_balance) VALUES
-- Admins (1-2)
('Admin User',        'admin@mail.com',     '123', 'Admin',         'https://ui-avatars.com/api/?name=Admin+User&background=FF6B35&color=fff&size=128',       0.00),
('System Admin',      'sysadmin@mail.com',  '123', 'Admin',         'https://ui-avatars.com/api/?name=System+Admin&background=E55A2B&color=fff&size=128',     0.00),
-- Customers (3-12)
('Ahmed Hassan',      'ahmed@mail.com',     '123', 'Customer',      'https://ui-avatars.com/api/?name=Ahmed+Hassan&background=4FC3F7&color=fff&size=128',     250.00),
('Layla Mostafa',     'layla@mail.com',     '123', 'Customer',      'https://ui-avatars.com/api/?name=Layla+Mostafa&background=EC407A&color=fff&size=128',     175.50),
('Omar Youssef',      'omar.y@mail.com',    '123', 'Customer',      'https://ui-avatars.com/api/?name=Omar+Youssef&background=7E57C2&color=fff&size=128',     320.00),
('Nada Ibrahim',      'nada@mail.com',      '123', 'Customer',      'https://ui-avatars.com/api/?name=Nada+Ibrahim&background=26A69A&color=fff&size=128',     80.00),
('Khaled Farouk',     'khaled@mail.com',    '123', 'Customer',      'https://ui-avatars.com/api/?name=Khaled+Farouk&background=42A5F5&color=fff&size=128',    450.00),
('Mona Saleh',        'mona@mail.com',      '123', 'Customer',      'https://ui-avatars.com/api/?name=Mona+Saleh&background=AB47BC&color=fff&size=128',       95.00),
('Yasser Mahmoud',    'yasser@mail.com',    '123', 'Customer',      'https://ui-avatars.com/api/?name=Yasser+Mahmoud&background=66BB6A&color=fff&size=128',   200.00),
('Dina Kamal',        'dina@mail.com',      '123', 'Customer',      'https://ui-avatars.com/api/?name=Dina+Kamal&background=FF7043&color=fff&size=128',       150.00),
('Tarek Nabil',       'tarek@mail.com',     '123', 'Customer',      'https://ui-avatars.com/api/?name=Tarek+Nabil&background=5C6BC0&color=fff&size=128',      60.00),
('Hana Adel',         'hana@mail.com',      '123', 'Customer',      'https://ui-avatars.com/api/?name=Hana+Adel&background=EF5350&color=fff&size=128',        310.00),
-- Kitchen Owners (13-20)
('Sara El-Masry',     'kitchen@mail.com',   '123', 'KitchenOwner',  'https://ui-avatars.com/api/?name=Sara+ElMasry&background=66BB6A&color=fff&size=128',     0.00),
('Nour Hassan',       'nour@mail.com',      '123', 'KitchenOwner',  'https://ui-avatars.com/api/?name=Nour+Hassan&background=26A69A&color=fff&size=128',      0.00),
('Fatma Ali',         'fatma@mail.com',     '123', 'KitchenOwner',  'https://ui-avatars.com/api/?name=Fatma+Ali&background=FF8A65&color=fff&size=128',        0.00),
('Amira Kamel',       'amira@mail.com',     '123', 'KitchenOwner',  'https://ui-avatars.com/api/?name=Amira+Kamel&background=BA68C8&color=fff&size=128',      0.00),
('Rania Saeed',       'rania@mail.com',     '123', 'KitchenOwner',  'https://ui-avatars.com/api/?name=Rania+Saeed&background=4DB6AC&color=fff&size=128',      0.00),
('Heba Magdy',        'heba@mail.com',      '123', 'KitchenOwner',  'https://ui-avatars.com/api/?name=Heba+Magdy&background=FFB74D&color=fff&size=128',       0.00),
('Yasmine Taha',      'yasmine@mail.com',   '123', 'KitchenOwner',  'https://ui-avatars.com/api/?name=Yasmine+Taha&background=E57373&color=fff&size=128',     0.00),
('Samira Fathi',      'samira@mail.com',    '123', 'KitchenOwner',  'https://ui-avatars.com/api/?name=Samira+Fathi&background=81C784&color=fff&size=128',     0.00),
-- Caterers (21-24)
('Caterer One',       'cat@mail.com',       '123', 'Caterer',       'https://ui-avatars.com/api/?name=Caterer+One&background=AB47BC&color=fff&size=128',      0.00),
('Royal Catering Co', 'royal@mail.com',     '123', 'Caterer',       'https://ui-avatars.com/api/?name=Royal+Catering&background=7E57C2&color=fff&size=128',   0.00),
('Elite Events',      'elite@mail.com',     '123', 'Caterer',       'https://ui-avatars.com/api/?name=Elite+Events&background=5C6BC0&color=fff&size=128',     0.00),
('Nile Feasts',       'nile@mail.com',      '123', 'Caterer',       'https://ui-avatars.com/api/?name=Nile+Feasts&background=4FC3F7&color=fff&size=128',      0.00),
-- Delivery Agents (25-28)
('Mahmoud Rider',     'del@mail.com',       '123', 'DeliveryAgent', 'https://ui-avatars.com/api/?name=Mahmoud+Rider&background=EF5350&color=fff&size=128',    0.00),
('Ali Express',       'ali.del@mail.com',   '123', 'DeliveryAgent', 'https://ui-avatars.com/api/?name=Ali+Express&background=FF7043&color=fff&size=128',      0.00),
('Hassan Speed',      'hassan.del@mail.com','123', 'DeliveryAgent', 'https://ui-avatars.com/api/?name=Hassan+Speed&background=FFA726&color=fff&size=128',     0.00),
('Karim Flash',       'karim.del@mail.com', '123', 'DeliveryAgent', 'https://ui-avatars.com/api/?name=Karim+Flash&background=42A5F5&color=fff&size=128',      0.00);

-- =========================
-- ROLE-SPECIFIC TABLES
-- =========================
INSERT INTO Admin (UserID) VALUES (1),(2);

INSERT INTO Customer (UserID, WalletBalance) VALUES
(3, 250.00),(4, 175.50),(5, 320.00),(6, 80.00),(7, 450.00),
(8, 95.00),(9, 200.00),(10, 150.00),(11, 60.00),(12, 310.00);

INSERT INTO KitchenOwner (UserID, KitchenName, Description, Status, VerifyStatus) VALUES
(13, 'Mama Kitchen',         'Authentic homemade Egyptian food made with love. Specializing in traditional recipes passed down through generations. Every dish tells a story of Egyptian heritage.',                                      'Active', 'Verified'),
(14, 'Nour\'s Delights',     'Fresh and healthy Mediterranean cuisine with a modern twist. Farm-to-table ingredients sourced from local Egyptian farms. Perfect for health-conscious foodies.',                                          'Active', 'Verified'),
(15, 'Fatma\'s Table',       'Grandmother\'s secret recipes brought to your doorstep. Authentic Upper Egyptian cuisine with rich flavors and generous portions. A taste of home in every bite.',                                         'Active', 'Verified'),
(16, 'Amira\'s Palace',      'Premium Syrian and Lebanese cuisine prepared with authentic spices. From creamy hummus to perfectly grilled meats, we bring Levantine flavors to Cairo.',                                                 'Active', 'Verified'),
(17, 'Rania\'s Sweets',      'Handcrafted Oriental desserts and pastries. From kunafa to baklava, each piece is a work of art. Perfect for celebrations and daily indulgence.',                                                          'Active', 'Verified'),
(18, 'Heba\'s Healthy Bites', 'Clean eating made delicious! Keto, vegan, and gluten-free options available. Meal prep packages for busy professionals who want to eat healthy without compromise.',                                     'Active', 'Verified'),
(19, 'Yasmine\'s Kitchen',    'Fusion cuisine blending Egyptian tradition with international flavors. Creative dishes that surprise and delight. Perfect for adventurous eaters.',                                                       'Active', 'Pending'),
(20, 'Samira\'s Seafood',     'Fresh seafood dishes from Alexandria\'s finest recipes. Grilled, fried, or steamed — we do it all. Daily catch specials and family platters available.',                                                   'Active', 'Pending');

INSERT INTO Caterer (UserID, BusinessName, Description) VALUES
(21, 'Golden Plate Catering', 'Professional catering for events of all sizes. Specializing in Egyptian and international cuisine with elegant presentation and impeccable service.'),
(22, 'Royal Catering Co',     'Luxury catering for weddings, galas, and VIP events. Award-winning chefs and a dedicated event planning team to make your celebration unforgettable.'),
(23, 'Elite Events Catering', 'Corporate catering specialists. From board meetings to company celebrations, we deliver professional service and exceptional food every time.'),
(24, 'Nile Feasts',           'Traditional Egyptian banquet catering. Perfect for large family gatherings, moulids, and cultural celebrations. Authentic taste, massive portions.');

INSERT INTO DeliveryAgent (UserID, VehicleType, PlateNumber, Status) VALUES
(25, 'Motorcycle', 'CAI-1234', 'Available'),
(26, 'Bicycle',    'N/A',      'Available'),
(27, 'Motorcycle', 'GIZ-5678', 'Busy'),
(28, 'Car',        'ALX-9012', 'Offline');

-- =========================
-- CATEGORIES (8 categories)
-- =========================
INSERT INTO Category (Name, Description) VALUES
('Main Meals',   'Hearty main courses and traditional Egyptian dishes'),
('Desserts',     'Sweet treats, Oriental pastries, and traditional desserts'),
('Appetizers',   'Starters, dips, and side dishes to kick off your meal'),
('Beverages',    'Fresh juices, smoothies, hot drinks, and traditional drinks'),
('Breakfast',    'Morning meals, brunch items, and Egyptian breakfast classics'),
('Seafood',      'Fresh fish, shrimp, calamari, and seafood platters'),
('Grills & BBQ', 'Premium grilled meats, kebabs, and BBQ specialties'),
('Healthy',      'Low-calorie, keto, vegan, and gluten-free options');

-- =========================
-- MENU ITEMS (50 items across kitchens)
-- =========================
INSERT INTO MenuItem (ItemName, ItemPrice, CategoryID, KitchenOwnerID, Description, Status) VALUES
-- Mama Kitchen (KO 1)
('Koshary',              30.00, 1, 1, 'Traditional Egyptian koshary with crispy onions, spicy tomato sauce, garlic vinegar, and chickpeas. A national favorite!', 'Available'),
('Molokhia with Rice',   45.00, 1, 1, 'Fresh molokhia with garlic served over fluffy Egyptian rice with grilled chicken pieces and crispy bread', 'Available'),
('Mahshi',               55.00, 1, 1, 'Stuffed grape leaves, peppers, and zucchini with seasoned rice, herbs, and a tangy tomato broth', 'Available'),
('Om Ali',               25.00, 2, 1, 'Classic Egyptian bread pudding with crushed nuts, raisins, coconut, and warm sweetened milk', 'Available'),
('Fattah',               60.00, 1, 1, 'Layers of crispy bread, seasoned rice, and tender lamb with garlic vinegar sauce. Festival favorite!', 'Available'),
('Fresh Mango Juice',    15.00, 4, 1, 'Freshly squeezed Egyptian mango juice, no sugar added, served ice cold', 'Available'),
('Foul Medames',         20.00, 5, 1, 'Traditional Egyptian fava beans with olive oil, cumin, lemon juice, and fresh herbs. Served with warm baladi bread', 'Available'),
('Basbousa',             18.00, 2, 1, 'Semolina cake soaked in sweet rose water syrup topped with almonds and hazelnuts', 'Available'),
-- Nour's Delights (KO 2)
('Mediterranean Bowl',   40.00, 1, 2, 'Fresh bowl with crispy falafel, creamy hummus, tabbouleh, grilled halloumi, and tahini drizzle', 'Available'),
('Quinoa Salad',         35.00, 3, 2, 'Healthy quinoa with roasted vegetables, crumbled feta cheese, pomegranate seeds, and lemon dressing', 'Available'),
('Kunafa',               30.00, 2, 2, 'Crispy golden kunafa with melted Akawi cheese filling, drizzled with sweet sugar syrup and crushed pistachios', 'Available'),
('Shakshuka',            28.00, 5, 2, 'Poached eggs in rich spiced tomato-pepper sauce with fresh herbs, served with toasted sourdough', 'Available'),
('Avocado Toast',        32.00, 5, 2, 'Smashed avocado on multigrain toast with cherry tomatoes, feta, za\'atar, and poached egg', 'Available'),
('Green Smoothie',       22.00, 4, 2, 'Spinach, banana, mango, chia seeds, and almond milk blended to perfection', 'Available'),
-- Fatma's Table (KO 3)
('Mulukhiyah Rabbit',    75.00, 1, 3, 'Authentic Upper Egyptian mulukhiyah cooked with whole rabbit, garlic, and coriander. Served with vermicelli rice', 'Available'),
('Feteer Meshaltet',     40.00, 5, 3, 'Flaky Egyptian layered pastry, baked golden and crispy. Available sweet with honey or savory with cheese', 'Available'),
('Hawawshi',             35.00, 1, 3, 'Spiced minced meat baked inside Egyptian baladi bread until crispy. Street food at its finest!', 'Available'),
('Roz Bel Laban',        20.00, 2, 3, 'Creamy Egyptian rice pudding with vanilla, cinnamon, and crushed pistachios. Served chilled', 'Available'),
('Kebda Iskandarani',    30.00, 3, 3, 'Alexandrian-style liver sautéed with peppers, garlic, and spices. Served with tahini and pickles', 'Available'),
-- Amira's Palace (KO 4)
('Chicken Shawarma Plate', 45.00, 7, 4, 'Marinated chicken shawarma with garlic sauce, pickled turnips, and seasoned fries', 'Available'),
('Hummus Trio',          35.00, 3, 4, 'Classic, roasted red pepper, and basil hummus served with warm pita and olive oil', 'Available'),
('Mixed Grill Platter',  95.00, 7, 4, 'Assorted grilled meats: lamb chops, chicken wings, kofta, and kabab. Served with rice and grilled vegetables', 'Available'),
('Lamb Mansaf',          85.00, 1, 4, 'Traditional Jordanian lamb cooked in fermented yogurt sauce, served over saffron rice with toasted almonds', 'Available'),
('Baklava Assorted',     28.00, 2, 4, 'Assorted Syrian baklava with walnuts, pistachios, and cashews. Layers of phyllo and syrup', 'Available'),
('Turkish Coffee',       12.00, 4, 4, 'Authentic Turkish coffee brewed in a traditional copper cezve. Choose from plain, medium, or sweet', 'Available'),
-- Rania's Sweets (KO 5)
('Kunafa Nabulsia',      35.00, 2, 5, 'Premium Nabulsi kunafa with stretchy cheese, golden vermicelli, and rose water syrup', 'Available'),
('Qatayef',              25.00, 2, 5, 'Traditional Ramadan pancakes filled with cream or nuts, fried and drizzled with syrup', 'Available'),
('Luqaimat',             20.00, 2, 5, 'Golden fried dough balls drizzled with date syrup and sprinkled with sesame seeds', 'Available'),
('Halawet El Jibn',      30.00, 2, 5, 'Sweet cheese rolls filled with ashta cream, topped with pistachios and rose syrup', 'Available'),
('Sahlab',               18.00, 4, 5, 'Traditional hot milk drink thickened with orchid flour, topped with cinnamon, coconut, and nuts', 'Available'),
('Mango Kunafa',         38.00, 2, 5, 'Our signature creation! Crispy kunafa layered with fresh mango cream and pistachios', 'Available'),
-- Heba's Healthy Bites (KO 6)
('Grilled Chicken Salad', 42.00, 8, 6, 'Herb-grilled chicken breast over mixed greens with cherry tomatoes, avocado, and balsamic dressing', 'Available'),
('Protein Power Bowl',   48.00, 8, 6, 'Grilled salmon, quinoa, edamame, sweet potato, and kale with tahini-lemon dressing', 'Available'),
('Keto Plate',           55.00, 8, 6, 'Grilled steak with cauliflower mash, sautéed mushrooms, and buttered asparagus. Zero carbs!', 'Available'),
('Acai Bowl',            38.00, 8, 6, 'Frozen acai blended smooth, topped with granola, fresh berries, coconut flakes, and honey', 'Available'),
('Detox Green Juice',    18.00, 4, 6, 'Cucumber, celery, ginger, lemon, and green apple. Fresh cold-pressed daily', 'Available'),
-- Yasmine's Kitchen (KO 7)
('Egyptian Sushi Roll',  50.00, 1, 7, 'Creative fusion: sushi rice with koshary toppings wrapped in nori. Served with spicy tomato dipping sauce', 'Available'),
('Tacos El Masry',       38.00, 1, 7, 'Soft corn tortillas filled with slow-cooked shawarma, tahini slaw, and pickled onions', 'Available'),
('Pharaoh Burger',       55.00, 1, 7, 'Wagyu beef patty with halloumi, caramelized onions, rocket, and special dukkah sauce in brioche bun', 'Available'),
('Lotus Cheesecake',     35.00, 2, 7, 'Creamy no-bake cheesecake with Lotus Biscoff crust and caramel drizzle', 'Available'),
('Passion Fruit Mojito', 22.00, 4, 7, 'Non-alcoholic passion fruit mojito with fresh mint, lime, and sparkling water', 'Available'),
-- Samira's Seafood (KO 8)
('Grilled Sea Bass',     80.00, 6, 8, 'Whole sea bass marinated with herbs and grilled to perfection. Served with tartar sauce and lemon rice', 'Available'),
('Shrimp Tagine',        70.00, 6, 8, 'Jumbo shrimp simmered in a rich tomato-pepper tagine with onions and fresh herbs', 'Available'),
('Calamari Rings',       40.00, 6, 8, 'Crispy golden calamari rings served with garlic aioli and lemon wedges', 'Available'),
('Seafood Platter',     120.00, 6, 8, 'Premium platter: grilled fish, shrimp, calamari, crab, and mussels. Feeds 2-3 people', 'Available'),
('Sayadeya Rice',        65.00, 6, 8, 'Traditional Egyptian fish and rice dish with caramelized onion sauce and roasted nuts', 'Available'),
('Fish & Chips',         45.00, 6, 8, 'Beer-battered fish fillet with crispy fries, coleslaw, and tartar sauce', 'Available');

-- Caterer Menu Items
INSERT INTO MenuItem (ItemName, ItemPrice, CategoryID, CatererID, Description, Status) VALUES
('Wedding Package Silver',  5000.00, 1, 1, 'Serves 100 guests. Includes 3 main courses, 2 desserts, beverages, and basic table setup', 'Available'),
('Wedding Package Gold',    8500.00, 1, 1, 'Serves 150 guests. Premium menu with 5 main courses, 3 desserts, live cooking stations, and elegant décor', 'Available'),
('Corporate Lunch Box',       45.00, 1, 3, 'Individual boxed lunch: sandwich, salad, fruit, dessert, and juice. Minimum order: 20 boxes', 'Available'),
('Birthday Party Package',  2000.00, 1, 4, 'Serves 50 guests. Includes main course, sides, birthday cake, decorations, and party supplies', 'Available');

-- =========================
-- PAYMENT METHODS
-- =========================
INSERT INTO Payment (Method) VALUES ('Cash'),('Card'),('Wallet'),('Online');

-- =========================
-- ORDERS (25 orders with varied statuses)
-- =========================
INSERT INTO `Order` (CustomerID, DeliveryAgentID, PaymentID, TotalPrice, LoyaltyPoints, OrderStatus, SpecialRequests, CreatedAt) VALUES
(1, 1, 1, 75.00,  15, 'Delivered',  'Extra spicy sauce please',                 '2026-02-10 10:30:00'),
(1, 1, 2, 85.00,  17, 'Delivered',  NULL,                                        '2026-02-09 14:00:00'),
(2, 2, 1, 120.00, 24, 'Delivered',  'No onions on the shawarma',                '2026-02-09 18:45:00'),
(3, 1, 3, 45.00,  9,  'Delivered',  NULL,                                        '2026-02-08 12:15:00'),
(4, 3, 2, 200.00, 40, 'Delivered',  'Birthday dinner - please add a candle!',   '2026-02-08 19:00:00'),
(5, 2, 4, 95.00,  19, 'Delivered',  NULL,                                        '2026-02-07 13:30:00'),
(1, 1, 1, 150.00, 30, 'Delivering', NULL,                                        '2026-02-11 20:00:00'),
(2, NULL, 2, 55.00,  11, 'Preparing',  'Gluten-free if possible',               '2026-02-12 00:15:00'),
(3, NULL, 1, 230.00, 46, 'Confirmed',  'Family gathering - need extra plates',  '2026-02-11 23:30:00'),
(6, NULL, 3, 40.00,  8,  'Pending',    NULL,                                    '2026-02-12 00:45:00'),
(7, NULL, 1, 68.00,  14, 'Pending',    'Extra tahini sauce',                    '2026-02-12 01:00:00'),
(8, NULL, 4, 92.00,  18, 'Pending',    NULL,                                    '2026-02-12 01:10:00'),
(1, 1, 2, 180.00, 36, 'Delivered',  NULL,                                        '2026-02-06 11:00:00'),
(3, 2, 1, 65.00,  13, 'Delivered',  'Less sugar in the juice please',           '2026-02-05 16:20:00'),
(4, NULL, 3, 110.00, 22, 'Cancelled',  'Changed plans, sorry',                  '2026-02-07 09:45:00'),
(9, 3, 1, 78.00,  16, 'Delivered',  NULL,                                        '2026-02-04 12:00:00'),
(10, 4, 2, 145.00, 29, 'Delivered', NULL,                                        '2026-02-03 14:30:00'),
(5, NULL, 1, 55.00,  11, 'Ready',     'Self pickup',                            '2026-02-11 22:00:00'),
(2, 1, 4, 320.00, 64, 'Delivered',  'Large family order',                       '2026-02-02 18:00:00'),
(7, NULL, 2, 42.00,  8,  'Preparing', NULL,                                     '2026-02-11 23:00:00'),
(6, NULL, 1, 88.00,  18, 'Confirmed', 'Pack each item separately please',       '2026-02-11 22:30:00'),
(9, 2, 3, 135.00, 27, 'Delivering', NULL,                                        '2026-02-11 21:15:00'),
(10, NULL, 1, 250.00, 50, 'Pending',  'Event order – delivering at 6 PM',       '2026-02-12 01:20:00'),
(8, 3, 2, 75.00,  15, 'Delivered',   NULL,                                       '2026-02-01 10:00:00'),
(4, 4, 1, 190.00, 38, 'Delivered',   'Include extra bread',                     '2026-01-30 17:00:00');

-- =========================
-- MENU ORDER ITEMS (link orders to menu items)
-- =========================
INSERT INTO MenuOrderItem (MenuItemID, OrderID, Quantity) VALUES
-- Order 1
(1, 1, 2), (6, 1, 1),
-- Order 2
(2, 2, 1), (8, 2, 2),
-- Order 3
(9, 3, 1), (11, 3, 1), (14, 3, 2),
-- Order 4
(12, 4, 1),
-- Order 5
(22, 5, 2), (24, 5, 1), (25, 5, 1),
-- Order 6
(32, 6, 1), (36, 6, 1),
-- Order 7
(3, 7, 2), (5, 7, 1), (7, 7, 1),
-- Order 8
(9, 8, 1), (10, 8, 1),
-- Order 9
(42, 9, 1), (43, 9, 2), (44, 9, 1),
-- Order 10
(12, 10, 1), (13, 10, 1),
-- Order 11
(21, 11, 1), (24, 11, 1),
-- Order 12
(33, 12, 1), (34, 12, 1),
-- Order 13
(22, 13, 2), (23, 13, 1),
-- Order 14
(15, 14, 1),
-- Order 15
(3, 15, 1), (4, 15, 2),
-- Order 16
(37, 16, 1), (38, 16, 1),
-- Order 17
(39, 17, 1), (41, 17, 1),
-- Order 18
(32, 18, 1),
-- Order 19
(45, 19, 1), (46, 19, 1), (44, 19, 2),
-- Order 20
(16, 20, 1), (17, 20, 1),
-- Order 21
(20, 21, 1), (21, 21, 1),
-- Order 22
(26, 22, 2), (27, 22, 1), (30, 22, 1),
-- Order 23
(42, 23, 2), (43, 23, 1), (46, 23, 1),
-- Order 24
(1, 24, 1), (2, 24, 1),
-- Order 25
(5, 25, 2), (4, 25, 3);

-- =========================
-- SUBSCRIPTIONS
-- =========================
INSERT INTO Subscription (CustomerID, PlanTime, Price, StartDate, EndDate, Status) VALUES
(1, 'Monthly',  200.00, '2026-02-01', '2026-03-01', 'Active'),
(1, 'Weekly',   60.00,  '2026-02-01', '2026-02-08', 'Active'),
(2, 'Monthly',  200.00, '2026-01-15', '2026-02-15', 'Active'),
(3, 'Weekly',   60.00,  '2026-02-05', '2026-02-12', 'Active'),
(5, 'Monthly',  200.00, '2026-01-01', '2026-02-01', 'Expired'),
(7, 'Daily',    25.00,  '2026-02-11', '2026-02-12', 'Active'),
(4, 'Weekly',   60.00,  '2026-01-20', '2026-01-27', 'Expired'),
(9, 'Monthly',  200.00, '2026-02-10', '2026-03-10', 'Active');

-- =========================
-- LIVE CHATS
-- =========================
INSERT INTO LiveChat (SenderID, ReceiverID, Message, Timestamp) VALUES
(3, 13, 'Hi! Can I customize my Koshary order? I want extra sauce.', '2026-02-11 10:00:00'),
(13, 3, 'Of course! We can add extra spicy sauce no problem.', '2026-02-11 10:02:00'),
(3, 13, 'Perfect, thank you so much! 😊', '2026-02-11 10:03:00'),
(4, 14, 'Do you offer meal prep packages for the week?', '2026-02-11 11:30:00'),
(14, 4, 'Yes! Check our subscription plans. We have weekly and monthly options.', '2026-02-11 11:32:00'),
(5, 16, 'Is the Mixed Grill Platter enough for 2 people?', '2026-02-10 14:00:00'),
(16, 5, 'Absolutely! It serves 2-3 people generously. Want me to add extra bread?', '2026-02-10 14:05:00'),
(5, 16, 'Yes please! And extra hummus too.', '2026-02-10 14:06:00'),
(7, 18, 'Are your keto meals truly zero carb?', '2026-02-11 09:15:00'),
(18, 7, 'Our keto meals are under 5g net carbs per serving. All nutritional info is on the menu!', '2026-02-11 09:20:00'),
(6, 20, 'What\'s the catch of the day?', '2026-02-11 15:00:00'),
(20, 6, 'Today we have fresh Red Sea grouper and Mediterranean sea bass! Both are excellent grilled.', '2026-02-11 15:03:00');

-- =========================
-- ADVERTISING
-- =========================
INSERT INTO Advertising (PaymentID, KitchenOwnerID, CatererID, Title, Description, StartDate, EndDate, Status) VALUES
(2, 1, NULL, 'Mama Kitchen Grand Opening Sale!',    '20% off all items for the first week! Try our authentic Egyptian dishes.', '2026-02-01', '2026-02-28', 'Active'),
(4, 4, NULL, 'Amira\'s Shawarma Festival',           'Buy 2 shawarma plates, get 1 free! Limited time offer.', '2026-02-10', '2026-02-20', 'Active'),
(2, NULL, 1, 'Golden Plate Wedding Season Special',  'Book your wedding catering before March and get 15% discount!', '2026-02-01', '2026-03-31', 'Active'),
(3, 6, NULL, 'Healthy New Year Challenge',            'Start your fitness journey with our meal prep packages. First week 30% off!', '2026-02-01', '2026-02-15', 'Active'),
(1, 5, NULL, 'Ramadan Kunafa Pre-Orders',             'Pre-order your Ramadan kunafa now and enjoy free delivery!', '2026-02-15', '2026-03-15', 'Inactive');

-- =========================
-- REVIEWS
-- =========================
INSERT INTO Review (CustomerID, KitchenOwnerID, CatererID, OrderID, Rating, Comment, CreatedAt) VALUES
(1, 1, NULL, 1,  5, 'Absolutely amazing koshary! Tastes just like my grandmother used to make. Will order again! 🔥', '2026-02-10 12:00:00'),
(1, 1, NULL, 2,  4, 'Molokhia was great but the basbousa was a little too sweet for my taste. Overall very good.', '2026-02-09 16:00:00'),
(2, 2, NULL, 3,  5, 'The Mediterranean Bowl is so fresh and delicious! Best healthy food in Cairo.', '2026-02-09 20:00:00'),
(3, 2, NULL, 4,  4, 'Shakshuka was perfectly cooked. Quick delivery too. Highly recommended!', '2026-02-08 13:30:00'),
(4, 4, NULL, 5,  5, 'Mixed Grill Platter was INCREDIBLE. Huge portions and everything was cooked to perfection!', '2026-02-08 21:00:00'),
(5, 6, NULL, 6,  5, 'Best healthy food service! The protein bowl is my new addiction. 💪', '2026-02-07 15:00:00'),
(1, 1, NULL, 13, 4, 'Always consistent quality. Mama Kitchen never disappoints!', '2026-02-06 13:00:00'),
(3, 3, NULL, 14, 5, 'Mulukhiyah rabbit was outstanding! Authentic Upper Egyptian flavors.', '2026-02-05 18:00:00'),
(9, 7, NULL, 16, 5, 'Egyptian Sushi? GENIUS! It actually tastes amazing. Must try!', '2026-02-04 14:00:00'),
(10, 7, NULL, 17, 4, 'Pharaoh Burger is next level. The dukkah sauce is incredible.', '2026-02-03 16:00:00'),
(2, 8, NULL, 19, 5, 'Seafood platter was super fresh and beautifully presented. Worth every pound!', '2026-02-02 20:00:00'),
(8, 1, NULL, 24, 4, 'Good food as always. Delivery was a bit late but the food made up for it.', '2026-02-01 12:00:00'),
(4, 4, NULL, 25, 5, 'Fattah was divine! Best I\'ve had outside of a wedding. 10/10', '2026-01-30 19:00:00'),
(6, 5, NULL, NULL, 5, 'Rania\'s kunafa is the best in Cairo, period. The mango kunafa is a masterpiece!', '2026-02-11 16:00:00'),
(7, 6, NULL, NULL, 4, 'Love the keto options! Finally a kitchen that understands clean eating.', '2026-02-11 10:00:00');

-- =========================
-- NOTIFICATIONS
-- =========================
INSERT INTO Notification (UserID, Title, Message, IsRead, Type, CreatedAt) VALUES
(3,  'Order Delivered! 🎉',    'Your order #1 has been delivered. Enjoy your meal!',            TRUE,  'Order',     '2026-02-10 11:00:00'),
(3,  'New Promotion! 🔥',      'Mama Kitchen: 20% off all items this week!',                   FALSE, 'Promotion', '2026-02-11 08:00:00'),
(3,  'Order on the way! 🚲',   'Your order #7 is being delivered by Mahmoud.',                  FALSE, 'Order',     '2026-02-11 20:30:00'),
(4,  'Order Delivered! 🎉',    'Your order #3 has been delivered. Rate your experience!',       TRUE,  'Order',     '2026-02-09 19:30:00'),
(4,  'Subscription Alert 📅',  'Your weekly subscription is expiring tomorrow.',                FALSE, 'System',    '2026-02-11 09:00:00'),
(5,  'Points Earned! ⭐',      'You earned 46 loyalty points from order #9!',                   FALSE, 'Order',     '2026-02-11 23:45:00'),
(13, 'New Order! 🔔',          'You received a new order from Ahmed Hassan.',                   FALSE, 'Order',     '2026-02-12 00:15:00'),
(13, 'Great Review! ⭐',       'Ahmed gave you 5 stars! "Absolutely amazing koshary!"',         TRUE,  'System',    '2026-02-10 12:05:00'),
(25, 'New Delivery! 🚲',       'You have a new delivery assignment for order #7.',              FALSE, 'Order',     '2026-02-11 20:10:00'),
(1,  'System Alert 🔧',        'New kitchen registration pending verification: Yasmine Kitchen', FALSE, 'System',    '2026-02-11 14:00:00'),
(21, 'Catering Request 📋',    'New wedding catering request from Layla for March 15.',         FALSE, 'Order',     '2026-02-11 12:00:00'),
(7,  'Welcome to BiteHub! 🎉', 'Thanks for subscribing! Your daily plan starts today.',         TRUE,  'System',    '2026-02-11 08:00:00');

-- =========================
-- CATERING REQUESTS
-- =========================
INSERT INTO CateringRequest (CustomerID, CatererID, EventType, EventDate, GuestCount, Budget, Details, Status) VALUES
(2, 1, 'Wedding',        '2026-03-15', 200, 15000.00, 'Egyptian-themed wedding reception. Need full service including setup, waitstaff, and traditional dishes. Venue: Marriott Cairo.', 'Pending'),
(5, 3, 'Corporate',      '2026-03-20', 80,  4000.00,  'Quarterly company celebration. Need boxed lunches and a dessert table. Dietary options required.', 'Accepted'),
(3, 2, 'Birthday Party',  '2026-02-28', 50,  3000.00,  '30th birthday party. Want a premium menu with live cooking station and custom cake.', 'Pending'),
(7, 4, 'Family Gathering','2026-03-01', 30,  2000.00,  'Family reunion dinner. Traditional Egyptian buffet style. Kids menu needed.', 'Accepted'),
(4, 1, 'Engagement',      '2026-04-10', 150, 12000.00, 'Engagement party with mixed Egyptian and Lebanese menu. Need elegant table setup.', 'Pending'),
(10, 2, 'Graduation',     '2026-06-15', 100, 8000.00,  'University graduation celebration. Modern fusion menu preferred.', 'Pending');

-- =========================
-- LOYALTY TRANSACTIONS
-- =========================
INSERT INTO LoyaltyTransaction (CustomerID, Points, Type, Description, CreatedAt) VALUES
(1, 15,  'Earned',   'Points from Order #1',                     '2026-02-10 12:00:00'),
(1, 17,  'Earned',   'Points from Order #2',                     '2026-02-09 16:00:00'),
(1, 30,  'Earned',   'Points from Order #7',                     '2026-02-11 20:30:00'),
(1, 36,  'Earned',   'Points from Order #13',                    '2026-02-06 13:00:00'),
(1, -50, 'Redeemed', 'Redeemed for 25 EGP discount on Order #7', '2026-02-11 19:55:00'),
(2, 24,  'Earned',   'Points from Order #3',                     '2026-02-09 20:00:00'),
(2, 64,  'Earned',   'Points from Order #19',                    '2026-02-02 20:00:00'),
(2, 50,  'Bonus',    'Welcome bonus for new subscription!',      '2026-01-15 08:00:00'),
(3, 9,   'Earned',   'Points from Order #4',                     '2026-02-08 13:30:00'),
(3, 46,  'Earned',   'Points from Order #9',                     '2026-02-11 23:45:00'),
(3, 13,  'Earned',   'Points from Order #14',                    '2026-02-05 18:00:00'),
(4, 40,  'Earned',   'Points from Order #5',                     '2026-02-08 21:00:00'),
(4, 38,  'Earned',   'Points from Order #25',                    '2026-01-30 19:00:00'),
(5, 19,  'Earned',   'Points from Order #6',                     '2026-02-07 15:00:00'),
(5, 25,  'Referral', 'Referral bonus: invited Khaled!',          '2026-01-25 10:00:00'),
(7, 14,  'Earned',   'Points from Order #11',                    '2026-02-12 01:00:00'),
(8, 18,  'Earned',   'Points from Order #12',                    '2026-02-12 01:10:00'),
(8, 15,  'Earned',   'Points from Order #24',                    '2026-02-01 12:00:00'),
(9, 16,  'Earned',   'Points from Order #16',                    '2026-02-04 14:00:00'),
(9, 27,  'Earned',   'Points from Order #22',                    '2026-02-11 21:15:00'),
(10, 29, 'Earned',   'Points from Order #17',                    '2026-02-03 16:00:00'),
(10, 50, 'Earned',   'Points from Order #23',                    '2026-02-12 01:20:00');

-- =========================
-- USER PHONES
-- =========================
INSERT INTO UserPhone (UserID, PhoneNumber) VALUES
(3,  '+20-100-123-4567'), (3,  '+20-112-987-6543'),
(4,  '+20-101-234-5678'),
(5,  '+20-100-345-6789'),
(6,  '+20-111-456-7890'),
(7,  '+20-102-567-8901'),
(8,  '+20-100-678-9012'),
(9,  '+20-112-789-0123'),
(10, '+20-101-890-1234'),
(11, '+20-100-901-2345'),
(12, '+20-111-012-3456'),
(13, '+20-111-987-6543'),
(14, '+20-100-876-5432'),
(15, '+20-112-765-4321'),
(16, '+20-101-654-3210'),
(17, '+20-100-543-2109'),
(18, '+20-111-432-1098'),
(21, '+20-102-321-0987'),
(22, '+20-100-210-9876'),
(25, '+20-112-109-8765'),
(26, '+20-101-098-7654');

-- =========================
-- USER ADDRESSES
-- =========================
INSERT INTO UserAddress (UserID, Address) VALUES
(3,  'Nasr City, Cairo'),
(3,  'Maadi, Cairo'),
(4,  'Heliopolis, Cairo'),
(5,  'Dokki, Giza'),
(6,  'Zamalek, Cairo'),
(7,  '6th October City, Giza'),
(8,  'New Cairo, Cairo'),
(9,  'Mohandessin, Giza'),
(10, 'Rehab City, New Cairo'),
(11, 'Madinaty, New Cairo'),
(12, 'Shubra, Cairo'),
(13, 'Maadi, Cairo'),
(14, 'Heliopolis, Cairo'),
(15, 'Mansoura, Dakahlia'),
(16, 'Zamalek, Cairo'),
(17, 'Nasr City, Cairo'),
(18, 'Sheikh Zayed, Giza'),
(19, 'New Cairo, Cairo'),
(20, 'Alexandria'),
(21, 'Nasr City, Cairo'),
(22, 'Garden City, Cairo'),
(23, 'Mohandessin, Giza'),
(25, 'Maadi, Cairo'),
(26, 'Dokki, Giza');

-- =========================
-- SUBSCRIPTION PAYMENTS
-- =========================
INSERT INTO SubscriptionPayment (PaymentID, SubscriptionID) VALUES
(2, 1), (2, 2), (4, 3), (1, 4), (3, 5), (1, 6), (2, 7), (4, 8);

-- =========================
-- MENU SUBSCRIPTIONS
-- =========================
INSERT INTO MenuSubscribe (SubscriptionID, MenuItemID) VALUES
(1, 1), (1, 2), (1, 3), (1, 9),
(2, 7), (2, 12), (2, 13),
(3, 9), (3, 10), (3, 14),
(4, 32), (4, 33), (4, 36),
(6, 1), (6, 9), (6, 32),
(8, 33), (8, 34), (8, 35), (8, 36);
