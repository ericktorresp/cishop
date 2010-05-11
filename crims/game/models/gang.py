from django.db import models
from system.models import Province, Robbery
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _
from game.thumbs import ImageWithThumbsField

ACTION_STATUS = (('planning', _('planning')), ('done', _('done')), ('aborted', _('aborted')),)

class Gang(models.Model):
    title = models.CharField(_('title'), max_length=100)
    presentation = models.TextField(_('presentation'))
    photo = ImageWithThumbsField(_('photo'), upload_to='uploads/gang', sizes=((100, 100),))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    province = models.ForeignKey(Province, verbose_name=_('province'))
    creater = models.ForeignKey(User, verbose_name=_('creater'), related_name='creater')
    leader = models.ForeignKey(User, verbose_name=_('leader'), related_name='leader', limit_choices_to={})
    vice_leader = models.ForeignKey(User, verbose_name=_('vice leader'), related_name='vice_leader', blank=True, null=True, limit_choices_to={})

    def save(self):
        need_create_gang_member = False
        if not self.pk:
            need_create_gang_member = True
            #@todo: new photo, elif photo, delete old one, upload new one.
        super(Gang, self).save()
        if need_create_gang_member:
            GangMember(user=self.creater, gang=self).save()
        
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('gang')
        verbose_name_plural = _('gangs')
        app_label = 'game'
        ordering = ['id', ]

class GangMember(models.Model):
    user = models.ForeignKey(User, verbose_name=_('user'))
    gang = models.ForeignKey(Gang, verbose_name=_('gang'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.user.username
    
    class Meta:
        app_label = 'game'
        verbose_name = _('member')
        verbose_name_plural = _('members')
        unique_together = ("user", "gang")
        #@todo: unique should be user is enough
        
class GangInvite(models.Model):
    inviter = models.ForeignKey(User, verbose_name=_('inviter'), related_name='inviter')
    accepter = models.ForeignKey(User, verbose_name=_('accepter'), related_name='accepter')
    gang = models.ForeignKey(Gang, verbose_name=_('gang'), related_name='gang')
    accepted = models.BooleanField(_('accepted'), default=False)
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.accepter.username
    
    class Meta:
        app_label = 'game'
        verbose_name = _('invite')
        verbose_name_plural = _('invites')
        
class GangNews(models.Model):
    subject = models.CharField(_('subject'), max_length=200)
    body = models.TextField(_('body'))
    gang = models.ForeignKey(Gang, verbose_name=_('gang'))
    writer = models.ForeignKey(User, verbose_name=_('writer'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.subject
    
    class Meta:
        app_label = 'game'
        verbose_name = _('news')
        verbose_name_plural = _('newses')

class GangRobbery(models.Model):
    gang = models.ForeignKey(Gang, verbose_name=_('gang'))
    robbery = models.ForeignKey(Robbery, verbose_name=_('robbery'), limit_choices_to={'type':'gang'})
    initiator = models.ForeignKey(User, verbose_name=_('initiator'))
    status = models.CharField(_('status'), max_length=10, choices=ACTION_STATUS, default='planning')
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    accepted = models.ManyToManyField(GangMember, verbose_name=_('accepted'), related_name='accepted', blank=True)
    declined = models.ManyToManyField(GangMember, verbose_name=_('declined'), related_name='declined', blank=True)
    
    def __unicode__(self):
        return self.robbery.title
    
    class Meta:
        app_label = 'game'
        verbose_name = _('robbery')
        verbose_name_plural = _('robberies')

class GangAssault(models.Model):
    gang = models.ForeignKey(Gang, verbose_name=_('gang'))
    victim = models.ForeignKey(User, verbose_name=_('victim'), related_name='victim')
    initiator = models.ForeignKey(User, verbose_name=_('initiator'), related_name='initiator')
    status = models.CharField(_('status'), max_length=10, choices=ACTION_STATUS, default='planning')
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    accepted = models.ManyToManyField(GangMember, related_name='assault_accepted', verbose_name=_('accepted'), blank=True)
    declined = models.ManyToManyField(GangMember, related_name='assault_declined', verbose_name=_('declined'), blank=True)
    
    def __unicode__(self):
        return self.victim.username
    
    class Meta:
        app_label = 'game'
        verbose_name = _('assault')
        verbose_name_plural = _('assaults')

#def create_leader(sender, instance, **kwargs):
#    leader = GangMember(gang_id=instance.id, user_id=instance.creater.id)
#    leader.save()
#
#models.signals.post_save.connect(create_leader, sender=Gang)
