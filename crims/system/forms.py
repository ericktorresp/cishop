# coding: utf-8
from django import forms
from game.models import *

class GangRobberyInlineForm(forms.ModelForm):
    class Meta:
        model = GangRobbery
    form.fields['initiator'].queryset = GangMember.objects.filter()
