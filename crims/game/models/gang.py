from django.db import models
from system.models import Province
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

class Gang(models.Model):
    title = models.CharField(_('title'), max_length=100)
    presentation = models.TextField(_('presentation'))
    photo = models.FileField(_('photo'), upload_to='uploads/gang')
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    province = models.ForeignKey(Province, verbose_name=_('province'))
    creater = models.ForeignKey(User, verbose_name=_('creater'), related_name='creater')
    leader = models.ForeignKey(User, verbose_name=_('leader'), related_name='leader', limit_choices_to={})
    vice_leader = models.ForeignKey(User, verbose_name=_('vice leader'), related_name='vice_leader', blank=True, null=True, limit_choices_to={})

    def save(self):
        need_create_gang_member = False
        if not self.pk:
            need_create_gang_member = True
        super(Gang, self).save()
        if need_create_gang_member:
            GangMember(user=self.creater, gang=self).save()
        
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('Gang')
        verbose_name_plural = _('Gangs')
        app_label = 'game'
        ordering = ['id', ]

class GangMember(models.Model):
    user = models.ForeignKey(User, verbose_name=_('User'))
    gang = models.ForeignKey(Gang, verbose_name=_('Gang'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.user.username
    
    class Meta:
        app_label = 'game'
        verbose_name = _('Gang Member')
        verbose_name_plural = _('Gang Members')
        unique_together = ("user", "gang")
        
class GangInvite(models.Model):
    inviter = models.ForeignKey(User, verbose_name=_('inviter'), related_name='inviter')
    accepter = models.ForeignKey(User, verbose_name=_('accepter'), related_name='accepter')
    gang = models.ForeignKey(Gang, verbose_name=_('Gang'), related_name='gang')
    accepted = models.BooleanField(_('accepted'), default=False)
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.accepter.username
    
    class Meta:
        app_label = 'game'
        verbose_name = _('Gang invite')
        verbose_name_plural = _('Gang invites')
        
class GangNews(models.Model):
    subject = models.CharField(_('subject'), max_length=200)
    body = models.TextField(_('body'))
    gang = models.ForeignKey(Gang, verbose_name=_('Gang'))
    writer = models.ForeignKey(User, verbose_name=_('writer'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.subject
    
    class Meta:
        app_label = 'game'
        verbose_name = _('Gang news')
        verbose_name_plural = _('Gang newses')
#def create_leader(sender, instance, **kwargs):
#    leader = GangMember(gang_id=instance.id, user_id=instance.creater.id)
#    leader.save()
#
#models.signals.post_save.connect(create_leader, sender=Gang)
