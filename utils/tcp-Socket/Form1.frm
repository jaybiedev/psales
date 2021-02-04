VERSION 5.00
Object = "{248DD890-BB45-11CF-9ABC-0080C7E7B78D}#1.0#0"; "MSWINSCK.OCX"
Object = "{AAC8DFAF-8A34-11D3-B327-000021C5C8A9}#1.0#0"; "SysTray.ocx"
Begin VB.Form f1 
   BackColor       =   &H8000000A&
   BorderStyle     =   4  'Fixed ToolWindow
   Caption         =   "TCP PRINTER UTILITY"
   ClientHeight    =   4575
   ClientLeft      =   45
   ClientTop       =   390
   ClientWidth     =   8175
   ControlBox      =   0   'False
   LinkTopic       =   "Form1"
   MaxButton       =   0   'False
   MinButton       =   0   'False
   ScaleHeight     =   4575
   ScaleWidth      =   8175
   ShowInTaskbar   =   0   'False
   StartUpPosition =   3  'Windows Default
   Visible         =   0   'False
   Begin VB.ComboBox protocol 
      BeginProperty Font 
         Name            =   "MS Sans Serif"
         Size            =   9.75
         Charset         =   0
         Weight          =   400
         Underline       =   0   'False
         Italic          =   0   'False
         Strikethrough   =   0   'False
      EndProperty
      Height          =   360
      ItemData        =   "Form1.frx":0000
      Left            =   0
      List            =   "Form1.frx":000A
      TabIndex        =   16
      Text            =   "TCP"
      Top             =   360
      Width           =   855
   End
   Begin VB.CommandButton Param 
      Caption         =   "Param"
      Height          =   405
      Left            =   7440
      TabIndex        =   14
      Top             =   360
      Width           =   735
   End
   Begin SysTrayCtl.cSysTray cSystray1 
      Left            =   7680
      Top             =   120
      _ExtentX        =   900
      _ExtentY        =   900
      InTray          =   -1  'True
      TrayIcon        =   "Form1.frx":0018
      TrayTip         =   "TCP-Printer Control."
   End
   Begin VB.TextBox filename 
      Height          =   375
      Left            =   2280
      TabIndex        =   11
      Text            =   "c:\printFolder\output.txt"
      Top             =   1200
      Width           =   4935
   End
   Begin VB.ComboBox output 
      Height          =   315
      ItemData        =   "Form1.frx":034A
      Left            =   120
      List            =   "Form1.frx":035D
      TabIndex        =   9
      Text            =   "PRN"
      Top             =   1200
      Width           =   2055
   End
   Begin VB.CommandButton update 
      Caption         =   "Update Port"
      Height          =   405
      Left            =   5760
      TabIndex        =   7
      Top             =   360
      Width           =   1575
   End
   Begin VB.TextBox hostName 
      Enabled         =   0   'False
      BeginProperty Font 
         Name            =   "Arial Black"
         Size            =   9.75
         Charset         =   0
         Weight          =   400
         Underline       =   0   'False
         Italic          =   0   'False
         Strikethrough   =   0   'False
      EndProperty
      Height          =   390
      Left            =   3000
      Locked          =   -1  'True
      TabIndex        =   5
      Top             =   360
      Width           =   1815
   End
   Begin VB.TextBox ipAddress 
      Enabled         =   0   'False
      BeginProperty Font 
         Name            =   "Arial Black"
         Size            =   9.75
         Charset         =   0
         Weight          =   400
         Underline       =   0   'False
         Italic          =   0   'False
         Strikethrough   =   0   'False
      EndProperty
      Height          =   390
      Left            =   840
      Locked          =   -1  'True
      TabIndex        =   4
      Top             =   360
      Width           =   2175
   End
   Begin VB.TextBox port 
      Alignment       =   1  'Right Justify
      BeginProperty Font 
         Name            =   "Arial Black"
         Size            =   9.75
         Charset         =   0
         Weight          =   400
         Underline       =   0   'False
         Italic          =   0   'False
         Strikethrough   =   0   'False
      EndProperty
      Height          =   390
      Left            =   4800
      TabIndex        =   1
      Top             =   360
      Width           =   855
   End
   Begin VB.TextBox Text1 
      Height          =   2775
      Left            =   120
      MultiLine       =   -1  'True
      ScrollBars      =   2  'Vertical
      TabIndex        =   0
      Top             =   1680
      Width           =   7935
   End
   Begin MSWinsockLib.Winsock Winsock1 
      Left            =   5040
      Top             =   3120
      _ExtentX        =   741
      _ExtentY        =   741
      _Version        =   393216
      LocalPort       =   5003
   End
   Begin VB.Label Label7 
      Caption         =   "Protocol"
      Height          =   255
      Left            =   120
      TabIndex        =   15
      Top             =   120
      Width           =   615
   End
   Begin VB.Label Label8 
      Height          =   255
      Left            =   2640
      TabIndex        =   13
      Top             =   0
      Width           =   615
   End
   Begin VB.Label Label6 
      Height          =   135
      Left            =   960
      TabIndex        =   12
      Top             =   0
      Width           =   735
   End
   Begin VB.Label Label5 
      Caption         =   "Filename"
      Height          =   375
      Left            =   2280
      TabIndex        =   10
      Top             =   840
      Width           =   2775
   End
   Begin VB.Label Label4 
      Caption         =   "Direct Output To:"
      Height          =   375
      Left            =   120
      TabIndex        =   8
      Top             =   840
      Width           =   2175
   End
   Begin VB.Label Label3 
      Caption         =   "Port"
      Height          =   405
      Left            =   5160
      TabIndex        =   6
      Top             =   120
      Width           =   1215
   End
   Begin VB.Label Label2 
      Caption         =   "Host Name"
      Height          =   375
      Left            =   3240
      TabIndex        =   3
      Top             =   120
      Width           =   1455
   End
   Begin VB.Label Label1 
      Caption         =   "IP Address"
      Height          =   495
      Left            =   1080
      TabIndex        =   2
      Top             =   120
      Width           =   1455
   End
End
Attribute VB_Name = "f1"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
' Experiment F
' Originally UDP Protocol
' On August 21 11:56PM Converted to TCP by default
' by : Jared O. Santibanez

'Project        :   Visual Basic Remote Print Server
'Author         :   Temujin Jumlani
'Email          :   whatever_tj@hotmail.com, tj@usls.edu
'Date           :   2003-03-03
'Description    :   A program that will receive string data from a port
'                   which will then be directed to a text or the printer.


Public message As String
Dim printData As String
Dim tempFilename
Dim dosPrint
Dim code
Dim value
Dim lnCtr
Dim sendToComPortFlag
Private Sub LoadPrinters()
Dim pr As Printer
For Each pr In Printers
   Combo1.AddItem pr.DeviceName
Next pr
Combo1.Text = Printer.DeviceName
End Sub
Private Sub Combo1_Click()
Set Printer = Printers(Combo1.ListIndex)
'Me.Label7 = Printers(Combo1.ListIndex)
'Me.Label7.Caption = "Printing to " & Printer.DeviceName
'Printer.Print "Hello"
End Sub

Private Sub Command2_Click()
Dim pr As Printer
For Each pr In Printers
   Combo1.AddItem pr.port  ' pr.DeviceName &
Next pr
Combo1.Text = Printer.DeviceName
End Sub
Private Sub update_Click()
    Me.Winsock1.Close
    
    If Me.protocol = "UDP" Then
        Winsock1.protocol = sckUDPProtocol
        Call Winsock1.Bind(Me.port)
        
    Else
        Winsock1.protocol = sckTCPProtocol
        Call Winsock1.Bind(Me.port)
        Call Winsock1.Listen
    End If
    
    
    
    'Me.Winsock1.Connect
End Sub

Private Sub cSysTray1_MouseDblClick(Button As Integer, Id As Long)
    f1.Visible = True
    f1.SetFocus
End Sub

Private Sub Form_Load()
    lnCtr = 0
    tempFilename = 0
    Me.port = 5003
    Me.hostName = Winsock1.LocalHostName
    Me.ipAddress = Winsock1.LocalIP
    Call Winsock1.Bind(5003)
    
    printData = ""
    sendToComPortFlag = 0
    code = Array("<reset>", "<bold>", "</bold>", "<normal>", "<small1>", "<small2>", "<small3>", "<header>", "</header>", "<break>", "<eject>", "<drawer>", "<cutter>", "<cutter1>", "<cutterm>", "<tall>", "</tall>", "<wide>", "</wide>")
    value = Array(Chr(27) & "@", Chr(27) & "E", Chr(27) & "F", Chr(27) & "F", Chr(27) & "m", Chr(27) & "p", Chr(15), Chr(27) & "W1", Chr(27) & "W0", vbCrLf, Chr(12), Chr(27) & Chr(112) & Chr(0) & Chr(48), Chr(27) & "d0", Chr(27) & "d1", Chr(27) & "m", Chr(27) & "!" & Chr(16), Chr(27) & "!" & Chr(15), Chr(27) & "!" & Chr(32), Chr(27) & "!" & Chr(31))
    
    Call Winsock1.Listen
    
End Sub
Private Sub Winsock1_Close()
  
  Winsock1.Close ' has to be called
  Winsock1.Listen ' listen again
End Sub
Private Sub Winsock1_ConnectionRequest(ByVal requestID As Long)

  If Winsock1.State = sckListening Then ' if the socket is listening
    Winsock1.Close ' reset its state to sckClosed
    Winsock1.Accept requestID ' accept the client
    
  End If
End Sub
Private Sub Form_Resize()
    Me.Text1.Width = Me.Width - 350
    Me.Text1.Height = Me.Height - 2270
End Sub

Private Sub Label6_Click()
  Winsock1.Close
  Unload Me
  End
 
End Sub



Private Sub Label8_Click()
    f1.Visible = False
End Sub

Private Sub Param_Click()
    Me.Text1 = "<reset>, <bold>, </bold>, <normal>, <small1>, <small2>, <small3>, <header>, </header>, <break>, <eject>, <drawer>, <cutter>, <cutter1>, <cutterm>"
    Me.Text1 = Me.Text1 + " <drawer0> -  " + "For cash drawer on COM1"
    Me.Text1 = Me.Text1 + " Chr(27) & @, Chr(27) & E, Chr(27) & F, Chr(27) & F, Chr(27) & M, Chr(27) & p, Chr(15), Chr(27) & W1, Chr(27) & W0, vbCrLf, Chr(12), Chr(27) & Chr(112) & Chr(0) & Chr(48), Chr(27) & d0, Chr(27) & d1, Chr(27) & m"
    
    
End Sub

Private Sub sock_Error(ByVal Number As Integer, Description As String, ByVal Scode As Long, ByVal Source As String, ByVal HelpFile As String, ByVal HelpContext As Long, CancelDisplay As Boolean)
  MsgBox "Socket Error " & Number & ": " & Description  ' show some "debug" info
  sock.Close ' close the erraneous connection
  sock.Listen ' listen again
End Sub
Private Sub Winsock1_DataArrival(ByVal bytesTotal As Long)
    
    Call Winsock1.GetData(message, vbString)
    
    
    If (message = "<drawer2>") Then
        Call OpenSerialDrawer
        
    ElseIf (message = "<drawer0>") Then
        Call OpenSerialDrawer0
        sendToComPortFlag = 0
    Else
        printData = printData & message
        Call PrintOut
  '  ElseIf (message = "eof" And sendToComPortFlag = 1) Then
  '      MsgBox ("1 " & message)
  '      Call OpenSerialDisplay1
  '      sendToComPortFlag = 0
  '  ElseIf (message = "eof") Then
  '       Call PrintOut
  '      printData = ""
  '      sendToComPortFlag = 0
  '  ElseIf (Trim(message) <> "") Then
  '
  '      If (message = "<display1>") Then
  '          sendToComPortFlag = 1
  '      ElseIf (message = "<display2>") Then
  '          sendToComPortFlag = 2
  '      Else
  '          printData = printData & message
  '      End If
    End If
    
    Exit Sub
    
   
ErrorHandler:
    MsgBox Err.Description
    
End Sub
    
Private Sub OpenSerialDrawer()
        Open "COM1" For Output As #2
        Print #2, Chr(91) & Chr(91) & Chr(91) & Chr(91) & Chr(91) & Chr(91)
        
    Close #2
    Me.Text1 = Me.Text1 + " Chr(1) sent to Open Serial Connection Cashbox at COM1..." + vbCrLf
    
End Sub
Private Sub OpenSerialDrawer0()
        Open "COM1" For Output As #2
        Print #2, Chr(0) & Chr(0) & Chr(0) & Chr(0) & Chr(0) & Chr(0) & Chr(0) & Chr(0) & Chr(0) & Chr(0) & Chr(0) & Chr(0)
    Close #2
    Me.Text1 = Me.Text1 + " Chr(91) sent to Open Serial Connection Cashbox at COM1..." + vbCrLf
End Sub
Private Sub OpenSerialDisplay1()
    Open "COM1" For Output As #2
    Print #2, printData
    Close #2
    Me.Text1 = Me.Text1 + " Display Data sent to COM1..." + vbCrLf
End Sub
    
Private Sub PrintOut()
    'MsgBox (printData)
    Me.Text1 = ""
    'On Error GoTo err1
    Me.Text1 = Me.Text1 + "Size of data: " & Len(printData) & vbCrLf
    
    If (Me.output = "Text File" And Trim(Me.filename) = "") Then
        MsgBox "Provide a filename"
        Exit Sub
        
    ElseIf (Me.output = "Text File" And Trim(Me.filename) <> "") Then
    
        thisTime = Year(Date) & "-" & Month(Date) & "-" & Day(Date) & "-"
        
        'If (dosPrint) Then
            Me.filename = "c:\printFolder\" & tempFilename & "-" & thisTime & ".txt"
            Me.filename.Refresh
        'Else
        
        'End If
        
        Me.Text1 = Me.Text1 + Me.filename + " written to c:\printFolder for printing..." + vbCrLf
        Open Me.filename For Output As #1
        For i = LBound(code) To UBound(code)
            Do While (InStr(1, printData, code(i)) > 0)
                printData = Replace(printData, code(i), value(i))
            Loop
        Next
        
    Else   'If (Me.output = "Printer") Then
        outputdevice = Me.output
        
        Me.Text1 = Me.Text1 + " Written to " + outputdevice + "..." + vbCrLf
        Open outputdevice For Output As #1
        'Open "PRN" For Output As #1
        'Open "Ne01" For Output As #1
        'Open "COM1" For Output As #1
        For i = LBound(code) To UBound(code)
            printData = Replace(printData, code(i), value(i))
        Next
    End If
    
    On Error GoTo err3
    Print #1, printData
    
    Close #1
    
    
    If (lnCtr > 20) Then
        lnCtr = 0
        Me.Text1 = ""
    End If
    
    
    lnCtr = lnCtr + 1
    'Me.Text1 = Me.Text1 + " Message Printed...." + vbCrLf + printData + vbCrLf
    'Me.Text1 = "Message Printed...."
    printData = ""
    Exit Sub
    
err1:
    Me.Text1 = Me.Text1 + Err.Description + vbCrLf
    Close #1
    Exit Sub
err2:
    Me.Text1 = Me.Text1 + Err.Description + vbCrLf
    Close #1
err3:
    Me.Text1 = Me.Text1 + Err.Description + vbCrLf
    Close #1
End Sub


