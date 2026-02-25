# Payment Gateway Setup Checklist

## Environment Configuration
- [ ] Set `PAYSTACK_PUBLIC_KEY` in `.env` (get from Paystack dashboard)
- [ ] Set `PAYSTACK_SECRET_KEY` in `.env`  
- [ ] Set `PAYSTACK_ENABLED=true` in `.env` (or via admin panel)
- [ ] OR set `KORA_PUBLIC_KEY`, `KORA_SECRET_KEY`, `KORA_ENABLED=true`
- [ ] OR set `STRIPE_PUBLIC_KEY`, `STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_ENABLED=true`

## Admin Panel Configuration (Alternative)
- [ ] Login as admin
- [ ] Navigate to Settings / Payment Gateways
- [ ] Configure desired gateway (Paystack, Kora, or Stripe)
- [ ] Enter public and secret keys
- [ ] Enable gateway
- [ ] Test API keys before saving

## Testing
- [ ] Access `/wallet/deposit` as authenticated user
- [ ] Enter test deposit amount (min 100 NGN)
- [ ] Verify redirect to selected payment gateway
- [ ] Complete test payment on gateway
- [ ] Verify callback returns to application
- [ ] Confirm wallet balance increased
- [ ] Check transaction record created with status `completed`
- [ ] Verify wallet ledger entry exists

## Webhook Configuration
### Paystack
- [ ] Go to Paystack Settings → API Keys & Webhooks
- [ ] Add webhook URL: `https://yourapp.com/webhooks/paystack`
- [ ] Events: Select `charge.success`
- [ ] Copy webhook secret (if needed)

### Kora
- [ ] Go to Kora Dashboard → Webhooks
- [ ] Add endpoint: `https://yourapp.com/webhooks/kora`
- [ ] Events: `charge.success`

### Stripe
- [ ] Go to Stripe Dashboard → Developers → Webhooks
- [ ] Add endpoint: `https://yourapp.com/webhooks/stripe`
- [ ] Events: `payment_intent.succeeded`
- [ ] Copy signing secret to `.env` as `STRIPE_WEBHOOK_SECRET`

## Local Development Testing
- [ ] Use sandbox/test credentials from payment gateway
- [ ] Test with test card numbers provided by gateway
- [ ] Monitor `storage/logs/laravel.log` for errors
- [ ] Verify transaction references are unique (`DEP_<unique>`)
- [ ] Test idempotency: trigger same webhook twice, verify one credit only

## Production Readiness
- [ ] Switch to production API keys in `.env` or admin panel
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure proper logging to external service
- [ ] Setup monitoring/alerts for failed payments
- [ ] Document support process for payment disputes
- [ ] Test webhook delivery with actual gateway test tools
- [ ] Monitor transaction logs daily
- [ ] Setup backup payment method (manual deposit for support)
- [ ] Verify SSL certificate is valid
- [ ] Test with real users on staging environment first

## Multi-Gateway Setup
If using multiple gateways simultaneously:
- [ ] Configure all gateway credentials in `.env` or admin panel
- [ ] Enable desired gateway as primary via `<GATEWAY>_ENABLED=true`
- [ ] System auto-selects first enabled gateway (Paystack → Kora → Stripe)
- [ ] Test each gateway independently
- [ ] Monitor logs to verify correct gateway is selected

## Troubleshooting
**Payment not redirecting:**
- Check `PAYSTACK_ENABLED=true` (or selected gateway enabled)
- Verify secret key is correct (test via SettingsController::testGateway)
- Check logs for initialization errors

**Callback not completed:**
- Verify webhook is being sent from gateway dashboard
- Check callback URL is publicly accessible
- Verify reference in transaction matches

**Wallet not credited:**
- Check transaction status is `pending` before callback
- Verify signature validation passes
- Check logs for database transaction errors

**Duplicate credits:**
- Should not occur - idempotency checks prevent duplicates
- If it happens: check for race conditions or manually verify transaction status

## Support Contacts
- **Paystack Support**: https://paystack.com/support
- **Kora Support**: https://korapay.com/support
- **Stripe Support**: https://support.stripe.com
