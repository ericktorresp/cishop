from django.db import models
from filebrowser.fields import FileBrowseField
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

BUSINESS_TYPE = (('bar', _('bar')), ('club', _('club')))

class Business(models.Model):
    title = models.CharField(_('title'), max_length=100)
    photo = FileBrowseField(_('photo'), max_length=200, directory="business/", format="Image", extensions=['.jpg', '.gif', '.png'])
    max_vistors = models.SmallIntegerField(_('max vistors'))
    price = models.IntegerField(_('price'))
    expend = models.IntegerField(_('expend'))
    type = models.CharField(_('type'), max_length=20, choices=BUSINESS_TYPE)
    limit = models.SmallIntegerField(_('limit'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
#    user_business = models.ManyToManyField(User, through=UserBusiness)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('business')
        verbose_name_plural = _('business')
        app_label = 'system'
        ordering = ['id']


