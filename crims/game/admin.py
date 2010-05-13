from game.models import *
from account.models import *
from system.models import *
from django.contrib import admin
from django.contrib.auth.admin import UserAdmin
from django import forms

class GangMemberInline(admin.StackedInline):
    model = GangMember
    extra = 3
    raw_id_fields = ['user']
    classes = ('collapse-closed',)

class GangNewsInlineForm(forms.ModelForm):
    def __init__(self, *args, **kwargs):
        super(GangNewsInlineForm, self).__init__(*args, **kwargs)
        self.fields['writer'].queryset = User.objects.filter(id__in=GangMember.objects.filter().values('user_id').query)

class GangNewsInline(admin.StackedInline):
    model = GangNews
    can_delete = False
    classes = ('collapse-closed',)
    form = GangNewsInlineForm
#    allow_add = True
#    def formfield_for_foreignkey(self, db_field, request, **kwargs):
#        if db_field.name == "writer":
#            kwargs["queryset"] = GangMember.objects.filter(gang=gang)
#            return db_field.formfield(**kwargs)
#        return super(GangNewsInline, self).formfield_for_foreignkey(db_field, request, **kwargs)

class GangInviteInlineForm(forms.ModelForm):
    def __init__(self, *args, **kwargs):
        super(GangInviteInlineForm, self).__init__(*args, **kwargs)
        self.fields['inviter'].queryset = User.objects.filter(id__in=GangMember.objects.filter().values('user_id').query)
        self.fields['accepter'].queryset = User.objects.exclude(id__in=GangMember.objects.filter().values('user_id').query)
        
class GangInviteInline(admin.StackedInline):
    form = GangInviteInlineForm
    model = GangInvite
    classes = ('collapse-closed',)

class GangChatInlineForm(forms.ModelForm):
    def __init__(self, *args, **kwargs):
        super(GangChatInlineForm, self).__init__(*args, **kwargs)
        self.fields['sender'].queryset = User.objects.filter(id__in=GangMember.objects.filter().values('user_id').query)

class GangChatInline(admin.StackedInline):
    form = GangChatInlineForm
    model = Chat
    fk_name = 'gang'
    classes = ('collapse-closed',)

class GangRobberyInlineForm(forms.ModelForm):
    def __init__(self, *args, **kwargs):
        super(GangRobberyInlineForm, self).__init__(*args, **kwargs)
        self.fields['initiator'].queryset = User.objects.filter(id__in=GangMember.objects.filter().values('user_id').query)

class GangRobberyInline(admin.StackedInline):
    form = GangRobberyInlineForm
    model = GangRobbery
    classes = ('collapse-closed',)

class GangAssaultInlineForm(forms.ModelForm):
    def __init__(self, *args, **kwargs):
        super(GangAssaultInlineForm, self).__init__(*args, **kwargs)
        self.fields['initiator'].queryset = User.objects.filter(id__in=GangMember.objects.filter().values('user_id').query)
        self.fields['victim'].queryset = User.objects.exclude(id__in=GangMember.objects.filter().values('user_id').query)

class GangAssaultInline(admin.StackedInline):
    form = GangAssaultInlineForm
    model = GangAssault
    classes = ('collapse-closed',)

class GangAdmin(admin.ModelAdmin):
    inlines = (GangMemberInline, GangNewsInline, GangInviteInline, GangChatInline, GangRobberyInline, GangAssaultInline,)
    raw_id_fields = ['creater', 'leader', 'vice_leader', ]
    list_display = ('title', 'creater', 'leader', 'vice_leader', 'created',)
#    prepopulated_fields = {"title": ("title",)}

class BountyAdmin(admin.ModelAdmin):
    raw_id_fields = ['sponsor', 'target']
    list_display = ('sponsor', 'target', 'credits', 'expired', 'completed',)

class BankInline(admin.StackedInline):
    model = Bank
    fk_name = 'user'
    can_delete = False
    classes = ('collapse-open',)

class ArmorInline(admin.StackedInline):
    model = UserArmor
    fk_name = 'user'
    can_delete = False
    classes = ('collapse-closed',)
    allow_add = True

class WeaponInline(admin.StackedInline):
    model = UserWeapon
    fk_name = 'user'
    can_delete = False
    classes = ('collapse-closed',)
    allow_add = True
    
class GuardInline(admin.StackedInline):
    model = UserGuard
    fk_name = 'user'
    can_delete = False
    classes = ('collapse-closed',)
    allow_add = True

class DrugInline(admin.StackedInline):
    model = UserDrug
    classes = ('collapse-closed',)
    allow_add = True

class HookerInline(admin.StackedInline):
    model = UserHooker
    classes = ('collapse-closed',)

class BuildingInline(admin.StackedInline):
    model = UserBuilding
    classes = ('collapse-closed',)
    
class BusinessDrugInline(admin.StackedInline):
    model = UserBusinessDrug
    
class BusinessInline(admin.StackedInline):
    model = UserBusiness
    classes = ('collapse-closed',)
    inlines = (BusinessDrugInline,)

class UserProfileInline(admin.StackedInline):
    model = UserProfile
    classes = ('collapse-closed',)

class UserDataInline(admin.StackedInline):
    model = UserData
    classes = ('collapse-closed',)
   
class ProfileAdmin(UserAdmin):
    inlines = (
        BankInline,
        ArmorInline,
        WeaponInline,
        GuardInline,
        DrugInline,
        HookerInline,
        BuildingInline,
        BusinessInline,
        UserProfileInline,
        UserDataInline,
    )

class PrisonAdmin(admin.ModelAdmin):
    list_display = ('prisoner', 'created', 'expired', 'escaped',)

class RipAdmin(admin.ModelAdmin):
    list_display = ('user', 'created', 'expired', 'escaped', 'reason', 'victim')

class UserBusinessWareInline(admin.StackedInline):
    pass

from record.models import UserBusinessLog
class UserBusinessLogInline(admin.StackedInline):
    model = UserBusinessLog
    max_num = 15
    classes = ('collapse-closed',)
    
class UserBusinessAdmin(admin.ModelAdmin):
    inlines = (UserBusinessLogInline,)
            
admin.site.register(UserBusiness, UserBusinessAdmin)
admin.site.register(Gang, GangAdmin)
admin.site.register(Bounty, BountyAdmin)
admin.site.unregister(User)
admin.site.register(User, ProfileAdmin)
admin.site.register(Guestbook)
admin.site.register(Prison, PrisonAdmin)
admin.site.register(Rip, RipAdmin)
admin.site.register(Challenge)
admin.site.register(SabotagePlan)
admin.site.register(UserFavorite)
