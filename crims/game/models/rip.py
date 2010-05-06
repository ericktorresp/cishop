from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

RIP_REASONS = (
   ('drug', _('drug')),
   ('assault', _('assault')),
   ('sick', _('sick')),
)

class Rip(models.Model):
    user = models.ForeignKey(User, verbose_name=_('user'), related_name='ripper')
    created = models.DateTimeField(_('created'), auto_now_add=True)
    expired = models.DateTimeField(_('expired'))
    escaped = models.BooleanField(_('escaped'), default=False)
    reason = models.CharField(_('reason'), max_length=20, choices=RIP_REASONS)
    victim = models.ForeignKey(User, verbose_name=_('victim'), blank=True, null=True, related_name='victimer')

    def __unicode__(self):
        return self.user.username
    
    class Meta:
        app_label = 'game'
        verbose_name = _('rip')
        verbose_name_plural = _('rip')
