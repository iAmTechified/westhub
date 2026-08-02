<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 md:px-8">
        
        <div class="mb-8">
            <h2 class="font-display text-2xl font-semibold text-primary-300 mb-2">Frequently Asked Questions (FAQs)</h2>
        </div>

        <div class="space-y-6" x-data="{ openAccordionItem: null }">
            @php
                $faqs = [
                    1 => [
                        'q' => 'How do I refer a patient to WestHub Healthcare for services?',
                        'a' => 'Referring a patient is a seamless process. You can reach our Referral Supervisors directly at 872-249-9191, fax clinical documentation to 872-268-8877, or send a secure email. For your convenience, we provide specialized Provider and Non-Provider referral forms on our website. Our team typically reviews and processes all requests within 24 business hours.'
                    ],
                    2 => [
                        'q' => 'What specific regions does WestHub Healthcare serve?',
                        'a' => 'We provide elite in-home care across seven major Illinois counties: Cook, Lake, DuPage, Will, Kankakee, Kendall, and Grundy. From the heart of Chicago to Barrington Township and surrounding areas, our care reaches wherever you call home.'
                    ],
                    3 => [
                        'q' => 'Who is eligible for WestHub’s in-home care services?',
                        'a' => 'Our care is designed for anyone prioritizing their recovery and independence. While we specialize in senior care, we also support individuals of all ages navigating injuries, chronic illnesses, or disabilities. Whether funded through long-term care insurance, workers\' compensation, or private arrangements, our services are structured to be both accessible and high-impact.'
                    ],
                    4 => [
                        'q' => 'Is WestHub Healthcare a licensed and insured agency?',
                        'a' => 'Absolutely. WestHub Healthcare is fully licensed by the Illinois Department of Public Health (IDPH). For your protection and peace of mind, every member of our team is bonded, insured, and covered by comprehensive professional liability insurance and workers\' compensation.'
                    ],
                    5 => [
                        'q' => 'How does WestHub select its caregiving team?',
                        'a' => 'We maintain a rigorous gold-standard screening process. Every WestHub professional undergoes an intensive background check; including criminal history, 10-year residency verification, and driving record audits alongside deep-dive professional reference checks. Only those who demonstrate clinical excellence and a heart for service join our team.'
                    ],
                    6 => [
                        'q' => 'What professional qualifications do WestHub caregivers hold?',
                        'a' => 'Our team consists of vetted, reliable professionals from diverse clinical backgrounds. Beyond their technical certifications, we hire individuals who are active in their local communities and possess a genuine passion for human connection. Many have personal experience in caregiving, ensuring they bring empathy and expertise to your home.'
                    ],
                    7 => [
                        'q' => 'What happens after I choose WestHub for my care?',
                        'a' => 'Your journey begins with a complimentary clinical consultation. One of our care managers will visit your home to understand your goals, routines, and specific needs. We then architect a bespoke care plan that aligns perfectly with your lifestyle and health objectives.'
                    ],
                    8 => [
                        'q' => 'When can I expect my caregiver to start?',
                        'a' => 'Because we prioritize the right "match" over a random assignment, we begin service only after your initial assessment. Once we identify the best professional for your needs, a member of our management team will personally introduce you to your caregiver on their first visit to ensure a smooth transition.'
                    ],
                    9 => [
                        'q' => 'What if the assigned caregiver isn’t the right fit for my family?',
                        'a' => 'Your comfort and trust are non-negotiable. While we hire caregivers with the highest ethical standards, we understand that chemistry matters. If you feel a caregiver is not the right fit, we will provide additional coaching or provide a replacement unequivocally to ensure your peace of mind.'
                    ],
                    10 => [
                        'q' => 'Can a family member be hired as my WestHub caregiver?',
                        'a' => 'Yes. We believe family can be the ultimate support system. If a family member meets our rigorous training and experience requirements, they can formally apply to join the WestHub team. They must pass all background checks and adhere to our professional standards, and we offer advanced training to help them elevate their skills while caring for you.'
                    ],
                    11 => [
                        'q' => 'How can I reach WestHub after business hours?',
                        'a' => 'Healthcare doesn\'t stop at 5:00 PM, and neither do we. We maintain an On-Call Support Team available 24/7. Whether you have a clinical emergency or a caregiver is delayed, a WestHub representative is always just a phone call away.'
                    ],
                    12 => [
                        'q' => 'How quickly can care services begin?',
                        'a' => 'We are built for responsiveness. Depending on the complexity of the case, WestHub can often initiate services immediately. Typically, a care plan is active within 24 to 72 hours of your initial request and assessment.'
                    ],
                    13 => [
                        'q' => 'Is there a minimum commitment for hours or days per week?',
                        'a' => 'To ensure the highest quality of care and clinical continuity, we typically have a 4-hour minimum per visit. While we evaluate every situation individually, we generally recommend a 20-hour weekly minimum to maintain the best health outcomes for our clients.'
                    ],
                    14 => [
                        'q' => 'What is the policy for rescheduling or canceling a shift?',
                        'a' => 'We value the time of our clients and our professionals. We require 24 hours’ notice for shift changes to avoid charges. In the event of a true emergency, our team will work with you to find a fair and supportive solution.'
                    ],
                    15 => [
                        'q' => 'Who should I contact regarding billing, scheduling, or administration?',
                        'a' => 'Our administrative offices are open Monday through Friday, 9:00 AM to 5:00 PM, for non-urgent matters. For the fastest response regarding records or billing, we recommend calling during these hours. For urgent scheduling needs, our 24/7 on-call line remains available to assist you.'
                    ],
                ];
            @endphp

            @foreach($faqs as $index => $faq)
                <x-sub.accordion-item 
                    :index="str_pad($index, 2, '0', STR_PAD_LEFT)"
                    :question="$faq['q']"
                    :answer="$faq['a']"
                    :exclusive="true"
                    :item-key="'enquiries-faq-' . $index"
                />
            @endforeach
        </div>
    </div>
</section>
