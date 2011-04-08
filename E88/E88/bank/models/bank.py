from django.db import models
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User
from django.conf import settings

from home.models import Country, Province

class Bank(models.Model):
    code = models.CharField(_('code'), max_length=10)
    name = models.CharField(_('name'), max_length=30)
    logo = models.ImageField(upload_to='images/bank', verbose_name=_('logo'), max_length=100)
    
    def __unicode__(self):
        return self.name

    def img_logo(self):
        return '<img src="%s%s">' % (settings.MEDIA_URL, self.logo)
    
    img_logo.allow_tags=True
    img_logo.short_description = 'logo image'
    
    class Meta:
        app_label = 'bank'
        db_table = u'bank'
        verbose_name = _('Bank')
        verbose_name_plural = _('Banks')
