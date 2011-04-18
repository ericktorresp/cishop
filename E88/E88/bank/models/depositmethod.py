from django.db import models
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User
from django.conf import settings

DEPOSITMETHOD_STATUS = (
    (0, _('disabled')),
    (1, _('enabled')),
    (2, _('deleted')),
)
class DepositMethod(models.Model):
    name = models.CharField(_('name'), max_length=20)
    alias = models.CharField(_('alias'), max_length=10)
    currency = models.CharField(_('currency'), max_length=3)
    discriminator = models.CharField(_('discriminator'), max_length=10, choices=settings.PAYMENTMETHOD_TYPE)
    note = models.CharField(_('note'), max_length=255)
    instruction = models.TextField(_('instruction'), max_length=1000)
    status = models.SmallIntegerField(_('status'), max_length=1, choices=DEPOSITMETHOD_STATUS, default=0)
    url = models.URLField(_('url'), max_length=200, verify_exists=False)
    logo = models.ImageField(upload_to='images/payment', max_length=100)
    min_deposit = models.DecimalField(_('min deposit'), max_digits=14, decimal_places=4)
    max_deposit = models.DecimalField(_('max deposit'), max_digits=14, decimal_places=4)
    regex = models.TextField(_('regex for notice infomation'), max_length=1000, null=True, blank=True)
    notice_number = models.CharField(_('sender number'), max_length=15)
    adder = models.ForeignKey(User, editable=False)
    add_time = models.DateTimeField(_('add time'), editable=False, auto_now_add=True)
#    api_key = models.CharField(_('api key'), max_length=32)
    
    def __unicode__(self):
        return self.name

    def img_logo(self):
        return '<img src="%s%s">' % (settings.MEDIA_URL, self.logo)
    img_logo.allow_tags=True
    img_logo.short_description = 'logo image'
            
    class Meta:
        app_label = 'bank'
        db_table = u'deposit_method'
        verbose_name = _('deposit method')