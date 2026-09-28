-- Chacha Prime checkout payment methods
INSERT INTO payment_methods (name,provider,enabled,sort_order,created_at,updated_at)
SELECT 'Chacha Wallet','wallet',1,10,NOW(),NOW() WHERE NOT EXISTS (SELECT 1 FROM payment_methods WHERE provider='wallet');
INSERT INTO payment_methods (name,provider,enabled,sort_order,created_at,updated_at)
SELECT 'Cash on Delivery','cod',1,20,NOW(),NOW() WHERE NOT EXISTS (SELECT 1 FROM payment_methods WHERE provider='cod');
INSERT INTO payment_methods (name,provider,enabled,sort_order,created_at,updated_at)
SELECT 'Card','card',0,30,NOW(),NOW() WHERE NOT EXISTS (SELECT 1 FROM payment_methods WHERE provider='card');
