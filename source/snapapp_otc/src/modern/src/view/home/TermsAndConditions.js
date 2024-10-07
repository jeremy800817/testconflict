Ext.define('snap.view.home.TermsAndConditions', {
    extend: 'Ext.panel.Panel',
    xtype: 'termsandconditionsview',
    requires: [
        'Ext.container.Container'
    ],
    title: 'Terms and Conditions',
    profiles: {
        classic: {
            panel1Flex: 1,
            panelHeight: 100,
            panel2Flex: 2
        },
        neptune: {
            panel1Flex: 1,
            panelHeight: 100,
            panel2Flex: 2
        },
        graphite: {
            panel1Flex: 2,
            panelHeight: 110,
            panel2Flex: 3
        },
        'classic-material': {
            panel1Flex: 2,
            panelHeight: 110,
            panel2Flex: 3
        }
    },
    width: '100%',
    height: 400,
    cls: Ext.baseCSSPrefix + 'shadow',
    layout: {
        type: 'vbox',
        pack: 'start',
        align: 'stretch'
    },
    scrollable: true,
    bodyPadding: 10,

    defaults: {
        frame: true,
        bodyPadding: 10
    },
    userCls: 'transactionlisting-head',
    items: [
        // {
        //     // Style for migasit default            
        //     style: {
        //         'border': '2px solid #204A6D',
        //     },
        //     height: 60,
        //     margin: '0 0 0 0',
        //     items: [{
        //         xtype: 'container',
        //         scrollable: false,
        //         layout: 'hbox',
        //         defaults: {
        //             bodyPadding: '5',
        //         },
        //         items: [{
        //             html: '<h1>Terms and Conditions</h1>',
        //             style:{
        //                 'padding-left':'5px'
        //             }
        //         }]
        //     },]
        // },
        {
            xtype: 'displayfield',
            width: '99%',
            padding: '0 1 0 1',
            value: "<h5 style=' width:100%;line-height: normal;overflow: inherit; margin:0px 0 30px; font-size: 16px;color:#757575;text-align:justify'><span style='background:#fff;position: relative;top: 10px;text-align:justify'>Welcome to our site. We maintain this web site as a service to our members. By using our site, you are agreeing to comply with and be bound by the following terms of use. Please review the following terms carefully. If you do not agree to these terms, you should not use this site. </span></h5>",
            renderer:function(html){
                this.setHtml(html)
            }
        },
        {
            xtype: 'displayfield',
            width: '99%',
            padding: '0 1 0 1',
            value: "<ol style='text-align:justify;padding: 0px; margin: 0px 0px 10px 25px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 20px; background-color: rgb(255, 255, 255);'>" +
                "<li style='line-height: 27px;'><span style='color:#0099ff;'><span style='font-size:16px;'><strong>Acceptance of Agreement</strong>.</span></span><br> You agree to the terms and conditions outlined in this Terms of Use Agreement (" + '"Agreement"' + ") with respect to our site (the " + '"Site"' + "). This Agreement constitutes the entire and only agreement between us and you, and supersedes all prior or contemporaneous agreements, representations, warranties and understandings with respect to the Site, the content, products or services provided by or through the Site, and the subject matter of this Agreement. This Agreement may be amended at any time by us from time to time without specific notice to you. The latest Agreement will be posted on the Site, and you should review this Agreement prior to using the Site.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='font-size:16px;'><span style='color:#0099ff;'><strong>Copyright</strong>.</span></span><br> The content, organization, graphics, design, compilation, magnetic translation, digital conversion and other matters related to the Site are protected under applicable copyrights, trademarks and other proprietary (including but not limited to intellectual property) rights. The copying, redistribution, use or publication by you of any such matters or any part of the Site, except as allowed by Section 4, is strictly prohibited. You do not acquire ownership rights to any content, document or other materials viewed through the Site. The posting of information or materials on the Site does not constitute a waiver of any right in such information and materials.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='color:#0099ff;'><span style='font-size:16px;'><strong>Service Marks</strong>.</span></span><br> Products and names mentioned on the Site may be trademarks of their respective owners.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='color:#0099ff;'><span style='font-size:16px;'><strong>Limited Right to Use</strong>.</span></span><br> The viewing, printing or downloading of any content, graphic, form or document from the Site grants you only a limited, nonexclusive license for use solely by you for your own personal use and not for republication, distribution, assignment, sublicense, sale, preparation of derivative works or other use. No part of any content, form or document may be reproduced in any form or incorporated into any information retrieval system, electronic or mechanical, other than for your personal use (but not for resale or redistribution).&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='font-size:16px;'><span style='color:#0099ff;'><strong>Editing, Deleting and Modification</strong>.</span></span><br> We reserve the right in our sole discretion to edit or delete any documents, information or other content appearing on the Site.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='color:#0099ff;'><span style='font-size:16px;'><strong>Indemnification</strong>.</span></span><br> You agree to indemnify, defend and hold us and our partners, attorneys, staff, advertisers, product and service providers, and affiliates (collectively, " + '"Affiliated Parties"' + ") harmless from any liability, loss, claim and expense, including reasonable attorney's fees, related to your violation of this Agreement or use of the Site.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;''><span style='color:#0099ff;'><span style='font-size:16px;'><strong>Nontransferable</strong>.</span></span><br> Your right to use the Site is not transferable. Any password or right given to you to obtain information or documents is not transferable.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='color:#0099ff;''><span style='font-size:16px;'><strong>Disclaimer and Limits</strong>.</span></span><br> THE INFORMATION FROM OR THROUGH THE SITE ARE PROVIDED " + '"AS-IS,"' + " " + '"AS AVAILABLE,"' + " AND ALL WARRANTIES, EXPRESS OR IMPLIED, ARE DISCLAIMED (INCLUDING BUT NOT LIMITED TO THE DISCLAIMER OF ANY IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE). THE INFORMATION AND SERVICES MAY CONTAIN BUGS, ERRORS, PROBLEMS OR OTHER LIMITATIONS. WE AND OUR AFFILIATED PARTIES HAVE NO LIABILITY WHATSOEVER FOR YOUR USE OF ANY INFORMATION OR SERVICE. IN PARTICULAR, BUT NOT AS A LIMITATION THEREOF, WE AND OUR AFFILIATED PARTIES ARE NOT LIABLE FOR ANY INDIRECT, SPECIAL, INCIDENTAL OR CONSEQUENTIAL DAMAGES (INCLUDING DAMAGES FOR LOSS OF BUSINESS, LOSS OF PROFITS, LITIGATION, OR THE LIKE), WHETHER BASED ON BREACH OF CONTRACT, BREACH OF WARRANTY, TORT (INCLUDING NEGLIGENCE), PRODUCT LIABILITY OR OTHERWISE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGES. THE NEGATION OF DAMAGES SET FORTH ABOVE ARE FUNDAMENTAL ELEMENTS OF THE BASIS OF THE BARGAIN BETWEEN US AND YOU. THIS SITE AND THE PRODUCTS, SERVICES, AND INFORMATION PRESENTED WOULD NOT BE PROVIDED WITHOUT SUCH LIMITATIONS. NO ADVICE OR INFORMATION, WHETHER ORAL OR WRITTEN, OBTAINED BY YOU FROM US THROUGH THE SITE SHALL CREATE ANY WARRANTY, REPRESENTATION OR GUARANTEE NOT EXPRESSLY STATED IN THIS AGREEMENT. WE DO NOT PROVIDE LEGAL ADVICE NOR ENTER INTO ANY ATTORNEY-CLIENT RELATIONSHIP.&nbsp;<br> <br> ALL RESPONSIBILITY OR LIABILITY FOR ANY DAMAGES CAUSED BY VIRUSES CONTAINED WITHIN THE ELECTRONIC FILE CONTAINING THE FORM OR DOCUMENT IS DISCLAIMED. WE WILL NOT BE LIABLE TO YOU FOR ANY INCIDENTAL, SPECIAL OR CONSEQUENTIAL DAMAGES OF ANY KIND THAT MAY RESULT FROM USE OF OR INABILITY TO USE OUR SITE. OUR MAXIMUM LIABILITY TO YOU UNDER ALL CIRCUMSTANCES WILL BE EQUAL TO THE PURCHASE PRICE YOU PAY FOR ANY GOODS, SERVICES OR INFORMATION.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;''><span style='color:#0099ff;'><span style='font-size:16px;'><strong>Use of Information</strong>.</span></span><br> We reserve the right, and you authorize us, to the use and assignment of all information regarding Site uses by you and all information provided by you in any manner consistent with our Privacy Policy. All remarks, suggestions, ideas, graphics, or other information communicated by you to us through the Site (collectively, the " + '"Submission"' + ") will forever be the property of ACE GTP. ACE GTP will not be required to treat any Submission as confidential, and will not be liable for any ideas for its business (including without limitation, product, service or advertising ideas) and will not incur any liability as a result of any similarities that may appear in future ACE GTP products, services or operations. Without limitation, ACE GTP will have exclusive ownership of all present and future existing rights to the Submission of every kind and nature everywhere. ACE GTP will be entitled to use the Submission for any commercial or other purpose whatsoever, without compensation to you or any other person sending the Submission. You acknowledge that you are responsible for whatever material you submit, and you, not ACE GTP, have full responsibility for the message, including its legality, reliability, appropriateness, originality, and copyright.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='font-size:16px;'><span style='color:#0099ff;'><strong>Third-Party Services</strong>.</span></span><br> We may allow access to or advertise third-party product or service providers (" + '"Merchants"' + ") from which you may purchase certain goods or services. You understand that we do not operate or control the products or services offered by Merchants. Merchants are responsible for all aspects of order processing, fulfillment, billing and customer service. We are not a party to the transactions entered into between you and Merchants. You agree that use of such Merchants is AT YOUR SOLE RISK AND IS WITHOUT WARRANTIES OF ANY KIND BY US, EXPRESSED, IMPLIED OR OTHERWISE INCLUDING WARRANTIES OF TITLE, FITNESS FOR PURPOSE, MERCHANTABILITY OR NON-INFRINGEMENT. UNDER NO CIRCUMSTANCES ARE WE LIABLE FOR ANY DAMAGES ARISING FROM THE TRANSACTIONS BETWEEN YOU AND MERCHANTS OR FOR ANY INFORMATION APPEARING ON MERCHANT SITES OR ANY OTHER SITE LINKED TO OUR SITE.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='color:#0099ff;'><span style='font-size:16px;'><strong>Third-Party Merchant Policies</strong>.</span></span><br> All rules, policies (including privacy policies) and operating procedures of Merchants will apply to you while on such sites. We are not responsible for information provided by you to Merchants. We and the Merchants are independent contractors and neither party has authority to make any representations or commitments on behalf of the other.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='font-size:16px;'><span style='color:#0099ff;'><strong>Privacy Policy</strong>.</span></span><br> Our Privacy Policy, as it may change from time to time, is a part of this Agreement.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='color:#0099ff;'><span style='font-size:16px;'><strong>Payments</strong>.</span></span><br> You represent and warrant that if you are purchasing something from us or from Merchants that (i) any credit information you supply is true and complete, (ii) charges incurred by you will be honored by your credit card company, and (iii) you will pay the charges incurred by you at the posted prices, including any applicable taxes.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='color:#0099ff;'><span style='font-size:16px;'><strong>Securities Laws</strong>.</span></span><br> This Site may include statements concerning our operations, prospects, strategies, financial condition, future economic performance and demand for our products or services, as well as our intentions, plans and objectives (particularly with respect to product and service offerings), that are forward-looking statements. These statements are based upon a number of assumptions and estimates which are subject to significant uncertainties, many of which are beyond our control. When used on our Site, words like " + '"anticipates,"' + " " + '"expects,"' + " " + '"believes,"' + " " + '"estimates,"' + " " + '"seeks,"' + " " + '"plans,"' + " " + '"intends,"' + " " + '"will"' + " and similar expressions are intended to identify forward-looking statements designed to fall within securities law safe harbors for forward-looking statements. The Site and the information contained herein does not constitute an offer or a solicitation of an offer for sale of any securities. None of the information contained herein is intended to be, and shall not be deemed to be, incorporated into any of our securities-related filings or documents.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='color:#0099ff;'><span style='font-size:16px;'><strong>Links to Other Web Sites</strong>.</span></span><br> The Site contains links to other web sites. We are not responsible for the content, accuracy or opinions express in such web sites, and such web sites are not investigated, monitored or checked for accuracy or completeness by us. Inclusion of any linked web site on our Site does not imply approval or endorsement of the linked web site by us. If you decide to leave our Site and access these third-party sites, you do so at your own risk.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='color:#0099ff;'><span style='font-size:16px;'><strong>Copyrights and Copyright Agents</strong>.</span></span><br> We respect the intellectual property of others, and we ask you to do the same. If you believe that your work has been copied in a way that constitutes copyright infringement, please provide our Copyright Agent the following information:&nbsp;<br> <br> (a) An electronic or physical signature of the person authorized to act on behalf of the owner of the copyright interest;&nbsp;<br> <br> (b) A description of the copyrighted work that you claim has been infringed;&nbsp;<br> <br> (c) A description of where the material that you claim is infringing is located on the Site;&nbsp;<br> <br> (d) Your address, telephone number, and email address;&nbsp;<br> <br> (e) A statement by you that you have a good faith belief that the disputed use is not authorized by the copyright owner, its agent, or the law; and&nbsp;<br> <br> (f) A statement by you, made under penalty of perjury, that the above information in your Notice is accurate and that you are the copyright owner or authorized to act on the copyright owner's behalf. Our Copyright Agent for Notice of claims of copyright infringement on the Site can be reached by directing an e-mail to the Copyright Agent at&nbsp;<a class='gamma' href='mailto:admin@ace2u.com' style='color: rgb(0, 154, 229); text-decoration: none;'>admin@ace2u.com</a>&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='font-size:16px;'><span style='color:#0099ff;'><strong>Proposed Product and Service Offerings</strong>.</span></span><br> All descriptions of proposed products and services are based on assumptions subject to change and you should not rely on the availability or functionality of products or services until they are actually offered through the Site. We reserve the right in its sole discretion to determine how registration and other promotions will be awarded. This determination includes, without limitation, the scope, nature and timing of all such awards.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='color:#0099ff;'><span style='font-size:16px;'><strong>Information and Press Releases</strong>.</span></span><br> The Site contains information and press releases about us. While this information was believed to be accurate as of the date prepared, we disclaim any duty or obligation to update this information or any press releases. Information about companies other than ours contained in the press release or otherwise, should not be relied upon as being provided or endorsed by us.&nbsp;<br> &nbsp;</li>" +
                "<li style='line-height: 27px;'><span style='color:#0099ff;'><span style='font-size:16px;'><strong>Miscellaneous</strong>.</span></span><br> This Agreement shall be treated as though it were executed and performed in Los Angeles, CA, and shall be governed by and construed in accordance with the laws of the State of California (without regard to conflict of law principles). Any cause of action by you with respect to the Site (and/or any information, products or services related thereto) must be instituted within one (1) year after the cause of action arose or be forever waived and barred. All actions shall be subject to the limitations set forth in Section 8 and Section 10. The language in this Agreement shall be interpreted as to its fair meaning and not strictly for or against either party. All legal proceedings arising out of or in connection with this Agreement shall be brought solely in Los Angeles, CA. You expressly submit to the exclusive jurisdiction of said courts and consents to extra-territorial service of process. Should any part of this Agreement be held invalid or unenforceable, that portion shall be construed consistent with applicable law and the remaining portions shall remain in full force and effect. To the extent that anything in or associated with the Site is in conflict or inconsistent with this Agreement, this Agreement shall take precedence. Our failure to enforce any provision of this Agreement shall not be deemed a waiver of such provision nor of the right to enforce such provision.</li>" +
                "</ol>",
                renderer:function(html){
                    this.setHtml(html)
                }
        },
    ]
});
