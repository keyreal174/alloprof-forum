<div class="richEditor" aria-label="{t c="Type your message"}>" data-id="{$editorData.editorID}" aria-describedby="{$editorData.editorDescriptionID}" role="textbox" aria-multiline="true">
    <p id="{$editorData.editorDescriptionID}" class="sr-only">
        {t c="richEditor.description.title"}
        {t c="richEditor.description.paragraphMenu"}
        {t c="richEditor.description.inlineMenu"}
    </p>
    <div class="richEditor-frame InputBox">
        <div class="js-richText">
            <div class="ql-editor richEditor-text userContent" data-gramm="false" contenteditable="true" data-placeholder="Create a new post..."></div>
        </div>
        <div class="js-InlineEditorToolbar"></div>
        <div class="js-ParagraphEditorToolbar"></div>
        <div class="richEditor-menu embedBar">
            <ul class="richEditor-menuItems" role="menubar" aria-label="{t c="Inline Level Formatting Menu"}">
                <li class="richEditor-menuItem" role="menuitem">
                    <div class="js-emojiHandle emojiMenu">
                        <button class="richEditor-button" type="button" aria-pressed="false">
                            <svg class="richEditorInline-icon" viewBox="0 0 24 24">
                                <title>{t c="Emoji"}</title>
                                <path fill="currentColor" d="M12,4 C7.58168889,4 4,7.58168889 4,12 C4,16.4181333 7.58168889,20 12,20 C16.4183111,20 20,16.4181333 20,12 C20,7.58168889 16.4183111,4 12,4 Z M12,18.6444444 C8.33631816,18.6444444 5.35555556,15.6636818 5.35555556,12 C5.35555556,8.33631816 8.33631816,5.35555556 12,5.35555556 C15.6636818,5.35555556 18.6444444,8.33631816 18.6444444,12 C18.6444444,15.6636818 15.6636818,18.6444444 12,18.6444444 Z M10.7059556,10.2024889 C10.7059556,9.51253333 10.1466667,8.95324444 9.45671111,8.95324444 C8.76675556,8.95324444 8.20746667,9.51253333 8.20746667,10.2024889 C8.20746667,10.8924444 8.76675556,11.4517333 9.45671111,11.4517333 C10.1466667,11.4517333 10.7059556,10.8924444 10.7059556,10.2024889 Z M14.5432889,8.95306667 C13.8533333,8.95306667 13.2940444,9.51235556 13.2940444,10.2023111 C13.2940444,10.8922667 13.8533333,11.4515556 14.5432889,11.4515556 C15.2332444,11.4515556 15.7925333,10.8922667 15.7925333,10.2023111 C15.7925333,9.51235556 15.2332444,8.95306667 14.5432889,8.95306667 Z M14.7397333,14.1898667 C14.5767111,14.0812444 14.3564444,14.1256889 14.2471111,14.2883556 C14.2165333,14.3336889 13.4823111,15.4012444 11.9998222,15.4012444 C10.5198222,15.4012444 9.7856,14.3374222 9.75271111,14.2885333 C9.64444444,14.1256889 9.42471111,14.0803556 9.2608,14.1884444 C9.09688889,14.2963556 9.05155556,14.5169778 9.15964444,14.6810667 C9.19804444,14.7393778 10.1242667,16.1125333 11.9998222,16.1125333 C13.8752,16.1125333 14.8014222,14.7395556 14.84,14.6810667 C14.9477333,14.5173333 14.9027556,14.2983111 14.7397333,14.1898667 Z"></path>
                            </svg>
                        </button>
                    </div>
                </li>
                {if $editorData.hasUploadPermission}
                <li class="richEditor-menuItem" role="menuitem">
                    <button class="richEditor-button richEditor-buttonUpload js-fakeFileUpload" type="button" aria-pressed="false">
                        <svg class="richEditorInline-icon" viewBox="0 0 24 24">
                            <title>{t c="Image"}</title>
                            <path fill="currentColor" fill-rule="nonzero" d="M3,5 L3,19 L21,19 L21,5 L3,5 Z M3,4 L21,4 C21.5522847,4 22,4.44771525 22,5 L22,19 C22,19.5522847 21.5522847,20 21,20 L3,20 C2.44771525,20 2,19.5522847 2,19 L2,5 C2,4.44771525 2.44771525,4 3,4 Z M4,18 L20,18 L20,13.7142857 L15.2272727,7.42857143 L10.5,13.7142857 L7.5,11.5 L4,16.5510204 L4,18 Z M7.41729323,10.2443609 C8.24572036,10.2443609 8.91729323,9.57278803 8.91729323,8.7443609 C8.91729323,7.91593378 8.24572036,7.2443609 7.41729323,7.2443609 C6.58886611,7.2443609 5.91729323,7.91593378 5.91729323,8.7443609 C5.91729323,9.57278803 6.58886611,10.2443609 7.41729323,10.2443609 Z"/>
                        </svg>
                        <input class="js-fileUpload richEditor-upload" type="file" accept="image/gif, image/jpeg, image/jpg, image/png">
                    </button>
                </li>
                {/if}
                <li class="richEditor-menuItem" role="menuitem">
                    <button class="richEditor-button" type="button" aria-pressed="false">
                        <svg class="richEditorInline-icon" viewBox="0 0 24 24">
                            <title>{t c="HTML View"}</title>
                            <path d="M4,5a.944.944,0,0,0-1,.875v12.25A.944.944,0,0,0,4,19H20a.944.944,0,0,0,1-.875V5.875A.944.944,0,0,0,20,5ZM4,4H20a1.9,1.9,0,0,1,2,1.778V18.222A1.9,1.9,0,0,1,20,20H4a1.9,1.9,0,0,1-2-1.778V5.778A1.9,1.9,0,0,1,4,4ZM9.981,16.382l-4.264-3.7V11.645L9.981,7.45V9.126l-3.2,2.958,3.2,2.605Zm4.326-1.693,3.2-2.605-3.2-2.958V7.45l4.265,4.195v1.041l-4.265,3.7Z" style="fill: currentColor"/>
                        </svg>
                    </button>
                </li>
                <li class="richEditor-menuItem isRightAligned" role="menuitem">
                    <button class="richEditor-button" type="button" aria-pressed="false">
                        <svg class="richEditorInline-icon" viewBox="0 0 24 24">
                            <title>{t c="Help"}</title>
                            <path fill="currentColor" d="M12,19 C15.8659932,19 19,15.8659932 19,12 C19,8.13400675 15.8659932,5 12,5 C8.13400675,5 5,8.13400675 5,12 C5,15.8659932 8.13400675,19 12,19 Z M12,20 C7.581722,20 4,16.418278 4,12 C4,7.581722 7.581722,4 12,4 C16.418278,4 20,7.581722 20,12 C20,16.418278 16.418278,20 12,20 Z M11.1336706,13.4973545 L11.1336706,13.1587302 C11.1336706,12.7707212 11.2042167,12.4479731 11.3453108,12.1904762 C11.486405,11.9329793 11.7333161,11.666668 12.0860516,11.3915344 C12.5058068,11.0599631 12.7765272,10.8024701 12.8982209,10.6190476 C13.0199146,10.4356252 13.0807606,10.2169325 13.0807606,9.96296296 C13.0807606,9.66666519 12.9819961,9.43915423 12.7844643,9.28042328 C12.5869324,9.12169233 12.3029847,9.04232804 11.9326124,9.04232804 C11.5975138,9.04232804 11.2871112,9.08994661 11.0013955,9.18518519 C10.7156798,9.28042376 10.437023,9.39506106 10.1654167,9.52910053 L9.72097222,8.5978836 C10.4370252,8.19929254 11.2042133,8 12.0225595,8 C12.713921,8 13.2624164,8.16931048 13.6680622,8.50793651 C14.0737079,8.84656254 14.2765278,9.31393 14.2765278,9.91005291 C14.2765278,10.1746045 14.2377275,10.4100519 14.1601257,10.6164021 C14.0825239,10.8227524 13.9652411,11.0193994 13.8082738,11.2063492 C13.6513065,11.393299 13.3805861,11.6366828 12.9961045,11.9365079 C12.6680605,12.1940048 12.448486,12.4074066 12.3373743,12.5767196 C12.2262627,12.7460326 12.1707077,12.9735435 12.1707077,13.2592593 L12.1707077,13.4973545 L11.1336706,13.4973545 Z M10.9167394,15.1851852 C10.9167394,14.6525547 11.1759961,14.3862434 11.6945172,14.3862434 C11.9484867,14.3862434 12.1424883,14.4559076 12.2765278,14.5952381 C12.4105672,14.7345686 12.477586,14.9312157 12.477586,15.1851852 C12.477586,15.4356274 12.4096854,15.6340381 12.2738823,15.7804233 C12.1380791,15.9268085 11.9449594,16 11.6945172,16 C11.444075,16 11.2518371,15.9285721 11.1177976,15.7857143 C10.9837581,15.6428564 10.9167394,15.4426821 10.9167394,15.1851852 Z"/>
                        </svg>
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class="richEditor-tempButtons" style="display: none;">
        <button type="button" class="test-sagan">
            Saganify!
        </button><br/>
        <button type="button" class="test-spoiler">
            Spoiler
        </button><br/>
        <button type="button" class="test-blockparagraph">
            Code Block - Paragraph
        </button><br/>
        <button type="button" class="clearQuill">
            ClearQuill
        </button><br/>
        <button type="button" class="test-blockquote">
            Blockquote
        </button><br/>
        <button type="button" class="test-loading">
            Embed - Loading
        </button><br/>
        <button type="button" class="test-error">
            Embed - Error
        </button><br/>
        <button type="button" class="test-image">
            Embed - Image
        </button><br/>
        <button type="button" class="test-video">
            Embed - Video
        </button><br/>
        <button type="button" class="test-urlinternal">
            Embed - Internal URL
        </button><br/>
        <button type="button" class="test-urlexternalimage">
            Embed - External URL With Image
        </button><br/>
        <button type="button" class="test-urlexternal">
            Embed - External URL Without Image
        </button>
    </div>
</div>
